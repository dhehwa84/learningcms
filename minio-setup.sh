#!/bin/bash
# minio-setup.sh

# Wait for MinIO to start
sleep 5

# Setup bucket and policies
/opt/bin/mc alias set myminio http://localhost:9000 minioadmin minioadmin123 --api S3v4

# Create bucket if it doesn't exist
/opt/bin/mc mb myminio/uploads --ignore-existing

# Set bucket policy to public read (adjust based on your needs)
/opt/bin/mc anonymous set download myminio/uploads

# Or for private bucket:
# /opt/bin/mc anonymous set private myminio/uploads

echo "MinIO setup completed"