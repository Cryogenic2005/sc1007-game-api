# SC1007 Game API

This repository contains the backend API for the SC1007 Unity game project.  
The API is built using PHP and the Slim framework, providing endpoints for user authentication and progress tracking.

## Prerequisites

- PHP >= 7.4
- Composer
- MySQL
- Apache or Nginx web server

## Installation

1. **Clone the repository:**

    ```bash
    git clone https://github.com/Cryogenic2005/sc1007-game-api.git
    cd sc1007-game-api

2. **Install dependencies using Composer:**

    ```bash
    composer install
    ```
    
3. **Configure the .env file with your environment variables (based on .env.cample)**

4. **Initialize the database:**

    ```sql
    CREATE DATABASE database_name;
    ```

    ```bash
    mysql -u root -p database_name < database_init.sql
    ```
    
5. **Run the API (for development):**

    If using PHP’s built-in server (for local dev):

    ```bash
    php -S localhost:8080 -t public
    ```

    If deploying with Apache/Nginx, ensure the `public/` folder is set as the document root.
