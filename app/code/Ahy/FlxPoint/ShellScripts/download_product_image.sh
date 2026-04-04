#!/bin/bash

# Check if the required arguments are provided
if [ "$#" -lt 1 ]; then
    echo "Usage: $0 <base_folder_path>"
    exit 1
fi

# Input parameter
BASE_FOLDER_PATH="$1"

# Define constants based on the base folder path
CSV_PATH="${BASE_FOLDER_PATH}var/flxPoint/Import-Process/product-catalog/pending/product-image.csv"
OUTPUT_CSV="${BASE_FOLDER_PATH}var/flxPoint/Import-Process/product-catalog/pending/product_image_path.csv"
IMAGE_DIR="${BASE_FOLDER_PATH}pub/media/catalog/product/flxpoint_images"
PREFIX_TO_REMOVE="${BASE_FOLDER_PATH}pub/media/catalog/product"

# Check if the CSV file exists
if [ ! -f "$CSV_PATH" ]; then
    echo "Error: CSV file not found at $CSV_PATH."
    exit 1
fi

# Create IMAGE_DIR if it does not exist
if [ ! -d "$IMAGE_DIR" ]; then
    echo "Creating directory $IMAGE_DIR..."
    mkdir -p "$IMAGE_DIR"
    if [ $? -ne 0 ]; then
        echo "Error: Failed to create directory $IMAGE_DIR. Exiting."
        exit 1
    fi
fi

# Create a new CSV file for SKU and file path
> "$OUTPUT_CSV"

# Read the SKU, base_image, and additional_images from the predefined CSV file
while IFS=',' read -r SKU BASE_IMAGE ADDITIONAL_IMAGES ; do
    # Skip the header line
    if [ "$SKU" == "sku" ]; then
        echo "$SKU,$BASE_IMAGE,$ADDITIONAL_IMAGES" >> "$OUTPUT_CSV"
        continue
    fi
    # Download base_image
    BASE_FILENAME=$IMAGE_DIR/$(basename "$BASE_IMAGE" | awk -F '?' '{print $1}')
    if [ -z "$BASE_IMAGE" ]; then
        echo "$SKU,$BASE_IMAGE,$ADDITIONAL_IMAGES" >> "$OUTPUT_CSV"
        echo "Warning: Base image URL not available for SKU $SKU. Skipping."
        continue
    fi

    if [ ! -f "$BASE_FILENAME" ]; then
        echo "Downloading base image for SKU $SKU..."
        if ! curl -o "$BASE_FILENAME" "$BASE_IMAGE"; then
            echo "Error: Failed to download base image for SKU $SKU. Skipping."
            BASE_FILENAME=""
        fi
    else
        echo "Base image for SKU $SKU already exists. Skipping download."
    fi

    # Split additional_images by either '|' or '"'
    IFS=' | ' read -ra ADDITIONAL_IMAGES_ARRAY <<< "$ADDITIONAL_IMAGES"

    # Array to store additional filenames
    ADDITIONAL_FILENAMES=()

    # Download additional images if they don't exist
    for ADDITIONAL_IMAGE in "${ADDITIONAL_IMAGES_ARRAY[@]}"; do
        ADDITIONAL_FILENAME=$IMAGE_DIR/$(basename "$ADDITIONAL_IMAGE" | awk -F '?' '{print $1}')
        # Add additional filename to the array
        ADDITIONAL_FILENAMES+=("/flxpoint_images/$(basename "$ADDITIONAL_FILENAME") |")
        if [ ! -f "$ADDITIONAL_FILENAME" ]; then
            echo "Downloading additional image for SKU $SKU..."
            if ! curl -o "$ADDITIONAL_FILENAME" "$ADDITIONAL_IMAGE"; then
                echo "Error: Failed to download additional image for SKU $SKU. Skipping."
                ADDITIONAL_FILENAMES[-1]=""  # Set the last entry to empty
            fi
        else
            echo "Additional image for SKU $SKU already exists. Skipping download."
        fi
    done

    # Concatenate additional filenames with ' | ' separator
    ADDITIONAL_FILENAMES_CONCAT=$(IFS=' | '; echo "${ADDITIONAL_FILENAMES[*]}")
    ADDITIONAL_FILENAMES_CONCAT=${ADDITIONAL_FILENAMES_CONCAT% |}  # Remove trailing ' |'

    # Remove the specified prefix from BASE_FILENAME
    BASE_FILENAME=${BASE_FILENAME#"$PREFIX_TO_REMOVE"}

    # Append SKU and file path to the output CSV
    echo "$SKU,$BASE_FILENAME,$ADDITIONAL_FILENAMES_CONCAT" >> "$OUTPUT_CSV"

done < "$CSV_PATH"

echo "Processing completed. Results saved in $OUTPUT_CSV"
