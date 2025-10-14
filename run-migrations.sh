#!/bin/bash

echo "Running database migrations..."

# Deploy a temporary job to run migrations
gcloud run jobs create migrate-db \
  --image=archive-playout \
  --region=us-central1 \
  --set-env-vars="APP_ENV=production,APP_DEBUG=false,APP_KEY=base64:E1XD+FCXtWFIEpxCQkwcsgmQFId2Xtwc14sNnTneWbY=,APP_URL=https://archive-playout-362374170433.us-central1.run.app,DB_CONNECTION=mysql,DB_HOST=/cloudsql/archive-playout-platform:us-central1:archive-playout-db,DB_PORT=3306,DB_DATABASE=archive_playout,DB_USERNAME=root,DB_PASSWORD=archive_Hq$2025,CACHE_DRIVER=file,QUEUE_CONNECTION=sync,SESSION_DRIVER=file" \
  --command="php,artisan,migrate,--force" \
  --set-cloudsql-instances="archive-playout-platform:us-central1:archive-playout-db" \
  --max-retries=3

echo "Migration job created. Check the Cloud Run jobs console for status." 