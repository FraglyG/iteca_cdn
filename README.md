# PHP Storage Server

A minimal PHP-based file storage server with JWT authentication.

## Features

- JWT-based authentication via cookies
- File upload with user-specific directories
- Direct file serving via URLs
- Support for common image formats (PNG, JPG, JPEG, GIF, WebP)

## Setup

### Option 1: Local Development
1. Configure your environment variables in `.env`:
   ```
   BACKEND_URL=your_backend_url
   BASE_URL=your_storage_server_url
   ```

2. Start the PHP development server:
   ```bash
   php -S localhost:8000
   ```

### Option 2: Docker
1. Configure your environment variables in `.env`:
   ```
   BACKEND_URL=your_backend_url
   BASE_URL=your_storage_server_url
   ```

2. Build and run with Docker:
   ```bash
   docker build -t php-storage .
   docker run -p 8000:80 --env-file .env -v ./data:/var/www/html/data php-storage
   ```

3. Or use Docker Compose:
   ```bash
   docker-compose up -d
   ```

## API Endpoints

### Upload File
- **POST** `/upload`
- Requires `accessToken` cookie for authentication
- Send file as `multipart/form-data` with field name `file`
- Returns JSON with file URL and filename

### View File
- **GET** `/{user_id}/{image_id}.{extension}`
- Serves the uploaded file directly

## Directory Structure

Files are stored in `/data/{user_id}/{random_id}.{extension}`
