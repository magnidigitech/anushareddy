# Deployment Guide

This guide explains how to set up and deploy the site both locally and in production using **Coolify**.

---

## 1. Local Development Setup

To run this application locally on your machine:

1. **Requirements**: Make sure you have PHP installed (PHP 8.0+ recommended) and optional local PostgreSQL.
2. **Setup Environment File**:
   Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
3. **Configure Database**:
   Open `.env` and fill in your database details.
   *(Note: If no database details are provided or if the database is offline, the site fallback systems will automatically read from `/data/products.json` and `/data/celebrities.json` so the frontend still loads.)*
4. **Start local server**:
   You can start PHP's built-in server in the project directory:
   ```bash
   php -S localhost:8000
   ```
   Now visit `http://localhost:8000` in your browser.

---

## 2. PostgreSQL Database Setup (Schema)

Before connecting your PostgreSQL database in Coolify or locally, initialize the database tables by executing the following SQL queries in your PostgreSQL query console:

```sql
-- 1. Create Products Table
CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(255),
    price_numeric INT NOT NULL,
    price VARCHAR(50),
    original_price_numeric INT,
    image TEXT, -- Stores JSON encoded arrays of image URLs
    description TEXT,
    details TEXT -- Stores JSON encoded arrays of description details
);

-- 2. Create Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    type VARCHAR(100),
    date VARCHAR(50),
    product_name VARCHAR(255),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Create Celebrities Table
CREATE TABLE IF NOT EXISTS celebrities (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL
);
```

---

## 3. Coolify Production Deployment Step-by-Step

### Step 1: Deploy a PostgreSQL Database (if not already existing)
1. Go to your **Coolify Dashboard** -> **Projects** -> Choose your project & environment.
2. Click **+ New Resource** and select **PostgreSQL** under the databases section.
3. Name the database (e.g. `anusha_reddy_db`) and click **Deploy**.
4. Once deployed, note down its internal connection details (Internal Host, Port, Database Name, Username, and Password).
5. Go to the PostgreSQL console/query tool in Coolify and execute the **Database Schema SQL** queries listed in Section 2 above to create your tables.

### Step 2: Deploy the Application
1. In your project environment, click **+ New Resource**.
2. Select **Public Repository** or **Private Repository** (e.g. GitHub app) depending on your Git host.
3. Select your repository and target branch.
4. Coolify will scan the codebase and automatically select the **Dockerfile** build pack since the repository contains a `Dockerfile`.
5. Enter a **Domain** for your site (e.g., `https://anushareddy.com` or a Coolify wildcard domain).

### Step 3: Configure Environment Variables
In your Coolify Application Dashboard, navigate to the **Environment Variables** tab and add the PostgreSQL details:
*   `DB_HOST` (e.g. `postgresql-xxxx-internal` or the internal database address)
*   `DB_PORT` (`5432`)
*   `DB_NAME` (Your database name, e.g. `postgres`)
*   `DB_USER` (Your database user, e.g. `postgres`)
*   `DB_PASSWORD` (Your database password)

### Step 4: Configure Persistent Volumes (CRITICAL)
Since container filesystems are ephemeral, any uploaded files or changes to local JSON databases will be deleted on redeployment unless mounted to a persistent volume.
1. In the Application settings, go to the **Storage / Volumes** tab.
2. Add the following directory mounts to preserve uploaded images and fallback data:
   *   `uploads-vol:/var/www/html/uploads`
   *   `data-vol:/var/www/html/data`
3. Click **Save**.

### Step 5: Build & Launch
1. Head to the top of your Application page and click **Deploy**.
2. Monitor the build logs. Once complete, your site will be live at your configured domain!
