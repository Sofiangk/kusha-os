# Docker & Koyeb Deployment Guide for KushaOS

## Part 1: Understanding Docker (Simple Explanation)

### What is Docker?

Think of Docker as a **portable box** that contains your entire application with everything it needs to run:

-   Your Laravel code
-   PHP 8.2
-   Nginx web server
-   All dependencies

**Why it's useful:**

-   Your app runs the **exact same** on your Mac, your friend's Windows, and Koyeb's server
-   No "it works on my machine" problems
-   Easy deployment: just build once, run anywhere

### Key Docker Files:

1. **Dockerfile** - Instructions to build your container

    - "Use PHP 8.2"
    - "Install dependencies"
    - "Copy my code"
    - "Start the web server"

2. **docker/nginx.conf** - Nginx web server config

    - Tells Nginx how to serve Laravel

3. **docker/start.sh** - Startup script
    - Runs migrations
    - Starts the server

### The Process (Simplified):

```
1. Build: Docker creates a container with your app
2. Run: Container starts and serves your API
3. Deploy: Koyeb takes your container and hosts it
```

---

## Part 2: Setup PlanetScale (Free MySQL Database)

### Step 1: Create Account

1. Go to https://planetscale.com
2. Sign up (free, no credit card)
3. Create a new organization (e.g., "My Projects")

### Step 2: Create Database

1. Click **"Create database"**
2. Name: `kushaos`
3. Region: Choose closest (e.g., `us-east`)
4. Click **"Create"**

### Step 3: Get Connection Info

1. Click on your database
2. Click **"Branches"** tab
3. You'll see `main` branch
4. Click **"Connect"** button
5. **Important**: Select **"Laravel"** as framework
6. Copy these credentials:

```
HOST: aws.connect.psdb.cloud
DATABASE: kushaos_main
USERNAME: abc123...
PASSWORD: pscale_pw_...
```

Save these! You'll use them in Koyeb.

### Step 4: Create Connection String

```
Format: mysql://USERNAME:PASSWORD@HOST/DATABASE?sslaccept=strict

Example:
mysql://abc123:pscale_pw_xyz@aws.connect.psdb.cloud:3306/kushaos_main?sslaccept=strict
```

---

## Part 3: Setup GitHub Repository

### Step 1: Initialize Git (if not done)

```bash
cd /Users/sofiangk/Code/kusha-os

# Check if git is initialized
git status

# If error, initialize git
git init
```

### Step 2: Create .gitignore (if missing)

```bash
# Make sure these files exist
cat .gitignore
# Should include: /vendor, /node_modules, .env, etc.
```

### Step 3: Commit Code

```bash
# Add all files
git add .

# Commit
git commit -m "Initial commit - KushaOS API ready for Koyeb"

# Create main branch (if not on main)
git branch -M main
```

### Step 4: Push to GitHub

```bash
# Create repository on GitHub first (github.com/new)
# Name it: kushaos
# Then push:

git remote add origin https://github.com/YOUR_USERNAME/kushaos.git
git push -u origin main
```

---

## Part 4: Deploy to Koyeb

### Step 1: Create Koyeb Account

1. Go to https://www.koyeb.com
2. Sign up (free, GitHub login works)
3. Skip credit card (not needed for free tier)

### Step 2: Create App

1. Dashboard > **"Create App"**
2. Choose **"GitHub"** as source
3. Authorize Koyeb to access GitHub
4. Select repository: `kushaos`
5. Branch: `main`

### Step 3: Configure Build

**Build Settings:**

-   Build command: (leave empty, Docker handles it)
-   Dockerfile path: `Dockerfile` (auto-detected)

**Environment Variables:**
Click **"Advanced"** and add these:

| Variable        | Value                        | Notes                                            |
| --------------- | ---------------------------- | ------------------------------------------------ |
| `APP_ENV`       | `production`                 |                                                  |
| `APP_DEBUG`     | `false`                      |                                                  |
| `APP_KEY`       | `base64:YOUR_RANDOM_KEY`     | Generate with: `php artisan key:generate --show` |
| `APP_URL`       | `https://YOUR_APP.koyeb.app` | Will get this after deployment                   |
| `DB_CONNECTION` | `mysql`                      |                                                  |
| `DB_HOST`       | `aws.connect.psdb.cloud`     | From PlanetScale                                 |
| `DB_PORT`       | `3306`                       |                                                  |
| `DB_DATABASE`   | `kushaos_main`               | From PlanetScale                                 |
| `DB_USERNAME`   | `abc123...`                  | From PlanetScale                                 |
| `DB_PASSWORD`   | `pscale_pw_...`              | From PlanetScale                                 |
| `LOG_CHANNEL`   | `errorlog`                   |                                                  |
| `LOG_LEVEL`     | `error`                      |                                                  |

### Step 4: Deploy

1. Click **"Deploy"**
2. Wait 2-5 minutes (Koyeb is building your container)
3. Watch the logs
4. Success! You'll get a URL like: `https://kushaos-xyz.koyeb.app`

### Step 5: Run Migrations

Koyeb will auto-run migrations if configured, but you can also run manually:

1. Go to your Koyeb app dashboard
2. Click **"Logs"** tab
3. Watch for migration output
4. If migrations failed, we'll fix in troubleshooting

---

## Part 5: Seed Database (Optional)

Since we can't SSH into Koyeb easily, we'll use Koyeb's **"Run Command"** feature:

1. Go to Koyeb dashboard
2. Your app > **"Logs"** tab
3. Click **"Run Command"** button
4. Enter: `php artisan db:seed --force`
5. Click **"Run"**
6. Wait for success message

---

## Part 6: Test Your API

Your API is now live at: `https://YOUR_APP.koyeb.app`

### Test Ping Endpoint:

```bash
curl https://YOUR_APP.koyeb.app/api/v1/ping
```

Expected response:

```json
{
    "status": "ok",
    "version": "v1",
    "time": "..."
}
```

### Test Login:

```bash
curl -X POST https://YOUR_APP.koyeb.app/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@kushaos.test","password":"password"}'
```

### Update Postman:

1. Open `KushaOS-API.postman_collection.json`
2. Update `base_url` environment variable to your Koyeb URL
3. Test all endpoints

---

## Part 7: Connect to Nuxt.js Frontend

In your Nuxt.js project:

### Update .env

```env
NUXT_PUBLIC_API_BASE_URL=https://YOUR_APP.koyeb.app/api/v1
```

### Update nuxt.config.ts

```typescript
export default defineNuxtConfig({
    runtimeConfig: {
        public: {
            apiBase:
                process.env.NUXT_PUBLIC_API_BASE_URL ||
                "http://localhost:8000/api/v1",
        },
    },
});
```

### Use in your components:

```typescript
const { $config } = useNuxtApp();
const response = await $fetch(`${$config.public.apiBase}/categories`);
```

---

## Troubleshooting

### Build Failed on Koyeb

-   Check logs in Koyeb dashboard
-   Common issues:
    -   `APP_KEY` not set
    -   Database credentials wrong
    -   PHP version mismatch

### Database Connection Failed

1. Check PlanetScale credentials
2. Verify SSL is enabled
3. Test connection string format
4. Check PlanetScale database is active

### Migrations Failed

1. Go to Koyeb Logs
2. Look for Laravel error messages
3. Common fix: `APP_KEY` not generated
    - Run: `php artisan key:generate --show`
    - Add to Koyeb environment variables

### 500 Server Error

1. Check Laravel logs in Koyeb
2. Common causes:
    - Storage permissions
    - Missing .env variables
    - Database connection

### How to View Logs

1. Koyeb dashboard > Your app > **"Logs"**
2. Filter by level (error, warning)
3. Real-time tail available

### How to Update Code

1. Push to GitHub (main branch)
2. Koyeb auto-detects changes
3. Rebuilds container
4. Re-deploys
5. Usually takes 2-5 minutes

---

## Cost Summary

**PlanetScale (Database):**

-   Free tier: 1 database, 1GB storage, free SSL
-   No credit card needed
-   Perfect for demos/small apps

**Koyeb (Hosting):**

-   Free tier: 1 instance, enough for demo
-   No credit card needed (usually)
-   Auto HTTPS included

**Total Cost: FREE** 💰

---

## Next Steps

1. ✅ Your API is live on Koyeb
2. ✅ Database running on PlanetScale
3. ✅ API endpoints tested
4. 🔲 Build Nuxt.js frontend
5. 🔲 Connect frontend to API
6. 🔲 Deploy frontend (Vercel/Netlify)

---

**Questions?**

-   Koyeb logs: Check "Logs" tab in dashboard
-   PlanetScale console: planetscale.com > Your database
-   Test API: Use Postman with your Koyeb URL

**Your demo is ready!** 🚀
