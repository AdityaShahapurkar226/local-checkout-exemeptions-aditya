#!/bin/bash

# Check if the required arguments are provided
if [ "$#" -lt 4 ]; then
    echo "Usage: $0 <base_folder_path> <mysql_user> <mysql_password> <mysql_database> <mysql_host>"
    exit 1
fi

# Input parameters
base_folder_path="$1"
mysql_user="$2"
mysql_password="$3"
mysql_database="$4"
mysql_host="$5"

# Set the path variables based on the base folder path
source_dir="${base_folder_path}var/flxPoint/Import-Process/product-catalog/pending/"
dest_dir="${base_folder_path}var/flxPoint/Import-Process/product-catalog/processed/"
sql_file="flxpoint_product_custom_import.sql"

# Get the current date, time, and month
current_date=$(date +"%Y-%m-%d")
current_time=$(date +"%H-%M-%S")
current_month=$(date +"%m")
current_year=$(date +"%Y")

# Set the destination folder and file names
dest_folder="processed-sql-file-$current_date"
dest_file="flxpoint_product_custom_import-$current_time.sql"
full_dest_path="$dest_dir$dest_folder/"

# Set the log directory and file paths
log_dir="${base_folder_path}var/flxPoint/Import-Process/product-catalog/log/"
log_file="$log_dir/import_log-$current_date.log"

# Create necessary directories if they don't exist
mkdir -p "$log_dir"
mkdir -p "$full_dest_path"

# Ensure the log directory is writable
chmod +w "$log_dir"

# Move old log files to a monthly archive directory and compress them
for file in "$log_dir"import_log-*; do
    if [[ -f "$file" ]]; then
        file_date=$(basename "$file" | sed -n 's/import_log-\([0-9]\{4\}-[0-9]\{2\}-[0-9]\{2\}\).log/\1/p')
        file_month=$(echo "$file_date" | cut -d'-' -f2)
        file_year=$(echo "$file_date" | cut -d'-' -f1)
        
        if [[ "$file_month" != "$current_month" || "$file_year" != "$current_year" ]]; then
            archive_folder="$log_dir/flxpoint-import-log-$file_month-$file_year/"
            mkdir -p "$archive_folder"
            mv "$file" "$archive_folder"
        fi
    fi
done

# Compress archived logs if any
for archive in "$log_dir"flxpoint-import-log-*; do
    if [[ -d "$archive" ]]; then
        tar -czf "$archive.tar.gz" -C "$log_dir" "$(basename "$archive")" && rm -rf "$archive"
    fi
done

# Change to the source directory
if ! cd "$source_dir"; then
    echo "Error: Source directory $source_dir does not exist." | tee -a "$log_file"
    exit 1
fi

# Check if the SQL file exists
if [ ! -f "$sql_file" ]; then
    echo "Error: File $source_dir$sql_file not found." | tee -a "$log_file"
    exit 1
fi

# Execute the MySQL command and log the output
{
    start_time=$(date +%s)
    echo "------------------------------ ***** START $(date +"%Y-%m-%d %H:%M:%S %Z") ***** ------------------------------"
    
    mysql -h "$mysql_host" -u "$mysql_user" -p"$mysql_password" "$mysql_database" < "$sql_file"
    mysql_exit_status=$?

    end_time=$(date +%s)
    duration_in_seconds=$((end_time - start_time))
    duration_in_minutes=$((duration_in_seconds / 60))
    remainder_seconds=$((duration_in_seconds % 60))

    echo "Total execution time: ${duration_in_minutes}m ${remainder_seconds}s"
    echo "------------------------------ ***** END $(date +"%Y-%m-%d %H:%M:%S %Z") ***** ------------------------------"
} >> "$log_file" 2>&1

# Check the MySQL command exit status
if [ $mysql_exit_status -eq 0 ]; then
    echo -e "\e[92mImport successfully completed\e[0m" | tee -a "$log_file"
    mv "$sql_file" "$full_dest_path$dest_file"
    echo -e "\e[92mMoved the SQL file to $full_dest_path$dest_file\e[0m" | tee -a "$log_file"
    
    # Move processed SQL files to an archive directory if they are from previous months
    for processed_file in "$dest_dir"processed-sql-file-*; do
        if [[ -d "$processed_file" ]]; then
            file_date=$(basename "$processed_file" | sed -n 's/processed-sql-file-\([0-9]\{4\}-[0-9]\{2\}-[0-9]\{2\}\)/\1/p')
            file_month=$(echo "$file_date" | cut -d'-' -f2)
            file_year=$(echo "$file_date" | cut -d'-' -f1)
            
            if [[ "$file_month" != "$current_month" || "$file_year" != "$current_year" ]]; then
                archive_folder="$dest_dir/flxpoint-processed-sql-$file_year-$file_month/"
                mkdir -p "$archive_folder"
                mv "$processed_file" "$archive_folder"
            fi
        fi
    done

    # Compress archived processed files if any
    for archive in "$dest_dir"flxpoint-processed-sql-*; do
        if [[ -d "$archive" ]]; then
            tar -czf "$archive.tar.gz" -C "$dest_dir" "$(basename "$archive")" && rm -rf "$archive"
        fi
    done
else
    echo -e "\e[41;97m<error>Error: SQL import failed. Check the log file: $log_file</error>\e[0m" | tee -a "$log_file"
    exit 1
fi
