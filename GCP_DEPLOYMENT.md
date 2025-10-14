# GCP Infrastructure Structure

## 🏗️ **Project Overview**
- **Project ID:** `archive-playout-platform`
- **Project Number:** `362374170433`
- **Region:** `us-central1`

## 🔧 **Core Services**

### **Cloud Run (Application Hosting)**
- **Service Name:** `archive-playout`
- **URL:** `https://archive-playout-362374170433.us-central1.run.app`
- **Memory:** 2Gi
- **CPU:** 1
- **Max Instances:** 10
- **Min Instances:** 0
- **Port:** 8080

### **Cloud SQL (Database)**
- **Instance Name:** `archive-playout-db`
- **Database:** `archive_playout`
- **Engine:** MySQL 8.0
- **Connection:** `/cloudsql/archive-playout-platform:us-central1:archive-playout-db`
- **Username:** `root`
- **Password:** `archive_Hq$2025`

### **Memorystore (Redis Cache)**
- **Instance Name:** `archive-playout-redis`
- **Host:** `10.0.0.3`
- **Port:** `6379`
- **Engine:** Redis 6.x

### **Cloud Storage (File Storage)**
- **Bucket:** `archive-playout-storage`
- **Location:** `us-central1`
- **Purpose:** File uploads, backups, static assets

## 🔐 **Service Accounts**

### **Cloud Build Deployer**
- **Name:** `cloud-build-deployer@archive-playout-platform.iam.gserviceaccount.com`
- **Purpose:** GitHub Actions deployment
- **Roles:**
  - `roles/run.admin`
  - `roles/cloudbuild.builds.builder`
  - `roles/cloudbuild.builds.editor`
  - `roles/serviceusage.serviceUsageConsumer`
  - `roles/artifactregistry.writer`
  - `roles/iam.serviceAccountUser`

### **Archive Playout Service Account**
- **Name:** `archive-playout-sa@archive-playout-platform.iam.gserviceaccount.com`
- **Purpose:** Application service account
- **Roles:**
  - `roles/cloudsql.client`
  - `roles/storage.objectCreator`
  - `roles/storage.objectViewer`

## 🚀 **CI/CD Pipeline**

### **GitHub Actions**
- **Repository:** `Astaan-Dev/archive-playout-backend`
- **Trigger:** Push to `main` branch
- **Workflow:** `.github/workflows/deploy.yml`
- **Authentication:** Service account key stored in GitHub Secrets

### **Deployment Process**
1. **Checkout code** from GitHub
2. **Authenticate** with Google Cloud
3. **Build Docker image** from source
4. **Deploy to Cloud Run** with environment variables
5. **Update service** with new container

## 🌐 **Networking**

### **VPC Network**
- **Network:** `default`
- **Subnet:** `default` in `us-central1`
- **Firewall:** Default rules applied

### **Cloud SQL Connection**
- **Private IP:** Enabled
- **Authorized Networks:** Cloud Run service account
- **Connection:** Unix socket via Cloud Run

## 📊 **Monitoring & Logging**

### **Cloud Logging**
- **Logs:** Application logs, Cloud Run logs
- **Retention:** 30 days (default)

### **Cloud Monitoring**
- **Metrics:** Cloud Run metrics, database metrics
- **Alerts:** Available for CPU, memory, errors

## 🔒 **Security**

### **IAM Permissions**
- **Least privilege** principle applied
- **Service accounts** for specific purposes
- **No public access** to database

### **SSL/TLS**
- **HTTPS:** Enabled by default
- **Certificate:** Managed by Google Cloud
- **Domain:** `*.run.app`

## 💰 **Cost Optimization**

### **Resource Limits**
- **Cloud Run:** Max 10 instances
- **Database:** Small instance (1 vCPU, 3.75 GB)
- **Redis:** Small instance (1 GB)

### **Scaling**
- **Auto-scaling:** Enabled
- **Cold starts:** Minimized with min instances
- **Cost control:** Resource limits in place

## 🛠️ **APIs Enabled**
- Cloud Run API
- Cloud SQL Admin API
- Cloud Build API
- Artifact Registry API
- Service Usage API
- Cloud Storage API
- Memorystore for Redis API

## 📝 **Quick Commands**

```bash

gcloud run services describe archive-playout --region=us-central1


gcloud logging read "resource.type=cloud_run_revision"


gcloud sql connect archive-playout-db --user=root


gsutil ls gs://archive-playout-storage
``` 