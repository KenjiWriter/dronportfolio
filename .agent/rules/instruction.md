---
trigger: always_on
---

# Project Instruction: Drone Photography Portfolio & Landing Page

## 1. Role & Objective
You are a Senior Laravel 12 & Vue 3 Developer. Your goal is to build a sophisticated, high-performance landing page with a custom CMS (Admin Panel) for a drone photographer and videographer, Łukasz Hil.

**Core Philosophy:** "Sophisticated Simplicity." The design must be minimalist but polished, featuring smooth animations and high-quality media handling.

## 2. Tech Stack & Environment
- **Framework:** Laravel 12 (latest).
- **Frontend:** Vue 3 (Composition API, `<script setup>`) + Tailwind CSS.
- **Architecture:** Monolith using Inertia.js (preferred for SPA feel) or Blade with Vue components.
- **Database:** MySQL/MariaDB or SQLite.

## 3. Key Deliverables

### A. Public Facing Landing Page (Single Page application feel)
1.  **Hero Section:**
    -   **Background:** Full-screen (`100vh`), looped, muted drone video (placeholder for now).
    -   **Overlay Content:** Centered text.
        -   Headline: "Łukasz Hil – Aerial Cinematography & Visuals"
        -   Sub-headline: "Professional Drone Services, Product Photography & Video Editing."
    -   **Actions:** Two buttons: "Portfolio" (scrolls to Projects) and "Contact" (scrolls to Contact).
    -   **Vibe:** Cinematic, immersive.

2.  **Portfolio Section (Realizations):**
    -   **Display Logic:** Grid of projects.
    -   **Project Types:**
        -   *Single Item:* Opens the media directly in a lightbox.
        -   *Catalog:* Represented visually as a "stack" of thumbnails (CSS 3D transform effect). Clicking opens a Modal/Lightbox containing the gallery of images/videos for that specific client.
    -   **Interactivity:** Smooth transitions, hover effects on thumbnails.

3.  **Contact Section:**
    -   **Info Block:**
        -   Mention: "Free consultations."
        -   Mention: "Travel costs included within Masovian Voivodeship (Woj. Mazowieckie)."
    -   **Lead Form:** Fields: Name, Company (optional), Location, Phone Number, Message.
    -   **Submission:** AJAX submission, saves to DB, shows success message.

4.  **Footer:**
    -   Simple copyright.
    -   **Mandatory Credit:** "Realization: Cerasus Digital" (link to `https://cerasusdigital.pl`). *Note: This credit must also be present in the HTML source code comments.*

### B. Admin Panel (Back-office)
1.  **Authentication:**
    -   Use Laravel default auth.
    -   **Strict Requirement:** DISABLE public registration routes (`/register`). Only the login page should be accessible.
    -   **Seeder:** Create a `UserSeeder` that generates a single Super Admin account.

2.  **Dashboard Features:**
    -   **Projects Management (CRUD):**
        -   Create generic "Projects" (Client Name / Title).
        -   Upload Cover Image.
        -   Upload Gallery Media (Images and Videos).
    -   **Leads Management:**
        -   View list of submitted contact forms (Leads).
        -   Status toggle (New / Contacted).

## 4. Database Schema Requirements

### `projects` table
- `id`, `title`, `slug`, `description` (nullable), `is_catalog` (boolean), `cover_image_path`, `timestamps`.

### `project_media` table
- `id`, `project_id` (FK), `file_path`, `file_type` (image/video), `sort_order`, `timestamps`.

### `leads` table
- `id`, `name`, `company_name`, `location`, `phone`, `message`, `status` (default: 'new'), `timestamps`.

## 5. Technical Constraints & Details
- **Smooth Scroll:** Implement native CSS smooth scrolling or Lenis.js for a premium feel.
- **Code Credit:** Add `` in the main layout file.
- **Routing:** Ensure the frontend feels seamless (Smooth scroll anchors for landing page sections).
- **Validation:** Strict validation on the Contact Form (phone number format, required fields).

## 6. First Step
Start by setting up the database migrations based on the schema above and the `UserSeeder` for the admin account. Then, outline the routes.