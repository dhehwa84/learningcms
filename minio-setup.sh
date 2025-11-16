#!/bin/bash
# minio-setup.sh

echo "Waiting for MinIO to be ready..."
# Simple sleep instead of curl check
sleep 10

echo "Setting up MinIO bucket..."

# Setup MinIO client alias
mc alias set myminio http://cmsminio:9000 minioadmin minioadmin123

# Create bucket if it doesn't exist
mc mb myminio/uploads --ignore-existing

# Set bucket policy to public
mc anonymous set public myminio/uploads

echo "MinIO setup completed - bucket 'uploads' is ready and public"