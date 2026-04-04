#!/bin/bash

# Set timezone to UTC or match PHP's timezone
export TZ="UTC"  # Or use "America/New_York" if that's what PHP is using

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

# Get the current date, time, and month
current_date=$(date +"%Y-%m-%d")
current_time=$(date +"%H-%M-%S")

# Set the path variables based on the base folder path
source_dir="${base_folder_path}var/BarcodeLookup/Import-Process/product-catalog/processed/$current_date"
sql_file="update-magento-catalog-products.sql"

# Set the destination folder and file names
dest_folder="processed-sql-files"
dest_file="barcode_lookup_product_custom_import-$current_time.sql"
full_dest_path="$source_dir/$dest_folder/"

# Set the log directory and file paths
log_dir="${base_folder_path}var/BarcodeLookup/Import-Process/product-catalog/processed/$current_date/logs"
log_file="$log_dir/import_log.log"

# Archive and Compress Folders Older Than 30 Days
processed_dir="${base_folder_path}var/BarcodeLookup/Import-Process/product-catalog/processed"
archive_folder="$processed_dir/archive"
compressed_folder="$processed_dir/archive_compressed"

# Create necessary directories if they don't exist
mkdir -p "$log_dir"
mkdir -p "$full_dest_path"

mkdir -p "$archive_folder"
mkdir -p "$compressed_folder"

# Ensure the log directory is writable
chmod +w "$log_dir"

# Change to the source directory
if ! cd "$source_dir"; then
    echo "Error: Source directory $source_dir does not exist." | tee -a "$log_file"
    exit 1
fi

# Check if the SQL file exists
if [ ! -f "$sql_file" ]; then
    error_message="Error: File $source_dir$sql_file not found."
    echo "$error_message" | tee -a "$log_file"
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

    # Move the SQL file to the processed folder
    mv "$sql_file" "$full_dest_path$dest_file"
    echo -e "\e[92mMoved the SQL file to $full_dest_path$dest_file\e[0m" | tee -a "$log_file"

    # Loop through all folders in processed directory
    for folder in "$processed_dir"/*; do
        folder_name=$(basename "$folder")
        
        # Skip current date folder and archive-related folders
        if [ "$folder_name" != "$current_date" ] && 
            [ "$folder_name" != "archive" ] && 
            [ "$folder_name" != "archive_compressed" ] && 
            [ -d "$folder" ]; then

            # Check if folder is older than 30 days
            folder_mod_time=$(stat -c %Y "$folder")
            current_time_epoch=$(date +%s)
            age_days=$(( (current_time_epoch - folder_mod_time) / 86400 ))

            if [ "$age_days" -gt 30 ]; then
                # Move to archive folder
                mv "$folder" "$archive_folder/"
                echo "Archived $folder_name to $archive_folder" | tee -a "$log_file"
            fi
        fi
    done

    # Compress archived folders older than 30 days
    for archive in "$archive_folder"/*; do
        # Skip if it's already a compressed file
        if [[ -d "$archive" ]]; then
            archive_mod_time=$(stat -c %Y "$archive")
            current_time_epoch=$(date +%s)
            age_days=$(( (current_time_epoch - archive_mod_time) / 86400 ))

            if [ "$age_days" -gt 30 ]; then
                tar -czf "$compressed_folder/$(basename "$archive").tar.gz" -C "$archive_folder" "$(basename "$archive")" && rm -rf "$archive"
                echo "Compressed $archive to $compressed_folder/$(basename "$archive").tar.gz" | tee -a "$log_file"
            fi
        fi
    done

    # Delete archive folder if it's empty
    if [ -d "$archive_folder" ] && [ -z "$(ls -A "$archive_folder")" ]; then
        rmdir "$archive_folder"
        echo "Deleted empty archive folder: $archive_folder" | tee -a "$log_file"
    fi

else
    echo -e "\e[41;97m<error>Error: SQL import failed. Check the log file: $log_file</error>\e[0m" | tee -a "$log_file"
    exit 1
fi
