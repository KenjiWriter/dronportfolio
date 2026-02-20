# Łukasz Hil - Drone Photography Portfolio

A sophisticated, high-performance landing page and portfolio for aerial cinematography and visual services. Built with a focus on "Sophisticated Simplicity," featuring smooth transitions and a premium admin panel.

## 🛠 Tech Stack

-   **Backend:** Laravel 12
-   **Frontend:** Vue 3 (Composition API)
-   **Glue:** Inertia.js
-   **Database:** MySQL
-   **Styling:** Tailwind CSS

## 🚀 Installation Guide

Follow these steps to set up the project locally:

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-username/dronportfolio.git
    cd dronportfolio
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Install Node.js dependencies:**
    ```bash
    npm install
    ```

4.  **Environment Configuration:**
    ```bash
    cp .env.example .env
    ```
    Configure your database credentials in the `.env` file.

5.  **Generate Application Key:**
    ```bash
    php artisan key:generate
    ```

6.  **Run Migrations & Seeders:**
    This command sets up the database schema and creates the default admin user.
    ```bash
    php artisan migrate --seed
    ```

7.  **Start Development Server:**
    ```bash
    npm run dev
    ```
    In a separate terminal:
    ```bash
    php artisan serve
    ```

## 🔐 Admin Credentials

To access the CMS/Admin Panel, use the following credentials:

-   **URL:** `/login`
-   **Email:** `admin@domain.pl`
-   **Password:** `password`

## 👨‍💻 Credits

Project Architecture & Development by **[Cerasus Digital](https://cerasusdigital.pl)**.
