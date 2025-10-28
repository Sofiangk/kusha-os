# Deployment Guide for KushaOS

> **Note**: For **FREE deployment**, use **Koyeb + PlanetScale** (see `DOCKER_GUIDE.md`)

This guide covers Heroku deployment (paid) and alternative free options.

This guide will help you deploy KushaOS (Laravel 12 API) to Heroku.

## Prerequisites

-   Heroku CLI installed: `brew install heroku/brew/heroku` (or download from heroku.com)
-   Git installed
-   GitHub account (recommended) or Heroku account

---

## Step 1: Create Heroku App

```bash
# Login to Heroku
heroku login

# Create the app
heroku create kushaos  # Replace 'kushaos' with your preferred name

# Or if you want a specific region
heroku create kushaos --region us
```

---

## Step 2: Add MySQL Database

```bash
# Add ClearDB MySQL add-on (free tier available)
heroku addons:create cleardb:ignite --app kushaos

# Get database credentials
heroku config --app kushaos | grep CLEARDB_DATABASE_URL
```

Save these credentials - you'll need them in step 4.

---

## Step 3: Configure Environment Variables

```bash
# Set app environment
heroku config:set APP_ENV=production --app kushaos
heroku config:set APP_DEBUG=false --app kushaos

# Set app key (will generate if not exists)
heroku config:set APP_KEY=$(php artisan key:generate --show) --app kushaos

# Configure database (from step 2)
heroku config:set DB_CONNECTION=mysql --app kushaos
heroku config:set DB_HOST=us-cdbr-iron-east-05.cleardb.net --app kushaos
heroku config:set DB_PORT=3306 --app kushaos
heroku config:set DB_DATABASE=your_database_name --app kushaos
heroku config:set DB_USERNAME=your_username --app kushaos
heroku config:set DB_PASSWORD=your_password --app kushaos

# Optional: Set other environment variables
heroku config:set APP_NAME="KushaOS" --app kushaos
heroku config:set LOG_LEVEL=error --app kushaos
```

---

## Step 4: Deploy to Heroku

### Option A: Direct Push (Quick)

```bash
# Add Heroku remote
heroku git:remote -a kushaos

# Push and deploy
git push heroku main
```

### Option B: Via GitHub (Recommended)

1. **Push to GitHub:**

```bash
# Initialize git if not already
git init

# Add and commit
git add .
git commit -m "Initial commit - KushaOS API"

# Add GitHub remote
git remote add origin https://github.com/YOUR_USERNAME/kushaos.git
git push -u origin main
```

2. **Connect Heroku to GitHub:**

```bash
# Via dashboard: heroku.com > Your app > Deploy > Connect to GitHub
# Or via CLI:
heroku git:remote -a kushaos
```

3. **Enable automatic deploys:**

-   Go to Heroku dashboard
-   Deploy tab > Connect to GitHub
-   Select repository > Enable automatic deploys from `main` branch

---

## Step 5: Run Migrations and Seed

```bash
# Run migrations
heroku run php artisan migrate --force --app kushaos

# Seed the database
heroku run php artisan db:seed --force --app kushaos
```

---

## Step 6: Verify Deployment

```bash
# Check your app logs
heroku logs --tail --app kushaos

# Visit your app
heroku open --app kushaos

# Test the ping endpoint
curl https://kushaos.herokuapp.com/api/v1/ping
```

Expected response:

```json
{
    "status": "ok",
    "version": "v1",
    "time": "2025-10-29T12:00:00"
}
```

---

## Step 7: Test API Endpoints

### Login

```bash
curl -X POST https://kushaos.herokuapp.com/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@kushaos.test","password":"password"}'
```

Save the token from the response.

### Test Authenticated Endpoint

```bash
curl -X GET https://kushaos.herokuos.herokuapp.com/api/v1/categories \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Post-Deployment

### Update Frontend Base URL

In your Nuxt.js app, update the API base URL:

```javascript
// nuxt.config.js or .env
API_BASE_URL=https://kushaos.herokuapp.com/api/v1
```

### Update Postman Collection

1. Open `KushaOS-API.postman_collection.json`
2. Update environment variable `base_url` to: `https://kushaos.herokuapp.com`
3. Save and use for testing production API

---

## Troubleshooting

### Database Connection Issues

```bash
# Check database credentials
heroku config --app kushaos

# Test database connection
heroku run php artisan db:show --app kushaos
```

### Migration Errors

```bash
# Rollback and re-run
heroku run php artisan migrate:rollback --force --app kushaos
heroku run php artisan migrate --force --app kushaos
```

### View Application Logs

```bash
heroku logs --tail --app kushaos
```

### Clear Cache

```bash
heroku run php artisan cache:clear --app kushaos
heroku run php artisan config:clear --app kushaos
heroku run php artisan route:clear --app kushaos
```

---

## Environment Variables Summary

| Variable        | Example Value | Required |
| --------------- | ------------- | -------- |
| `APP_ENV`       | `production`  | Yes      |
| `APP_DEBUG`     | `false`       | Yes      |
| `APP_KEY`       | Generated     | Yes      |
| `DB_CONNECTION` | `mysql`       | Yes      |
| `DB_HOST`       | From ClearDB  | Yes      |
| `DB_DATABASE`   | From ClearDB  | Yes      |
| `DB_USERNAME`   | From ClearDB  | Yes      |
| `DB_PASSWORD`   | From ClearDB  | Yes      |
| `LOG_LEVEL`     | `error`       | Optional |

---

## Production Checklist

-   [ ] Environment variables configured
-   [ ] Database migrations run
-   [ ] Database seeded
-   [ ] API ping endpoint working
-   [ ] Authentication working
-   [ ] Test creating an order
-   [ ] Test reports endpoint
-   [ ] Frontend base URL updated
-   [ ] Monitoring enabled (optional: Papertrail add-on)

---

## Free Alternatives to Heroku

If Heroku's free tier doesn't meet your needs, consider:

1. **Railway** - More generous free tier

    ```bash
    # Install Railway CLI
    npm i -g @railway/cli
    railway login
    railway init
    railway up
    ```

2. **Render** - Free MySQL + PHP

    - Connect GitHub repository
    - Auto-deploy on push

3. **Fly.io** - Docker-based deployment
    ```bash
    fly launch
    fly deploy
    ```

---

## Database Backup

```bash
# Export database
heroku pg:backups:capture --app kushaos
heroku pg:backups:download --app kushaos

# Restore from backup
heroku pg:backups:restore DATABASE_URL --app kushaos
```

---

## Useful Commands

```bash
# Scale dynos (free tier: 1 web dyno)
heroku ps:scale web=1 --app kushaos

# View running dynos
heroku ps --app kushaos

# Access Tinker
heroku run php artisan tinker --app kushaos

# Run any artisan command
heroku run php artisan YOUR_COMMAND --app kushaos
```

---

**Your API will be live at:** `https://kushaos.herokuapp.com/api/v1`

Ready for Nuxt.js frontend development! 🚀
