## 🛠️ CI/CD Pipeline Description

For every project, I follow a standardized and reliable CI/CD approach that ensures code quality, test coverage, and almost zero-downtime deployments by using clearly defined stages. The pipeline is run in GitLab using Kubernetes, and is designed to be perfect in avoiding mistakes, also helping QA.

### 🔄 Pipeline Flow

1. **Trigger:**  
   Every **Merge Request (MR)** triggers the CI/CD pipeline.


2. **Stage 1 – Code Quality & Testing:**  
   The first stage checks for:
    - Code style and linting (e.g., using Pint or PHPStan).
    - Unit and integration tests (using PHPUnit).

   Only if this stage passes, the pipeline proceeds.


3. **Stage 2 – Docker Build:**  
   The second stage builds a **Docker images** based on our containers using the updated code.


4. **Stage 3 – Dynamic Kubernetes Branch Deployment:**
    - A **temporary Kubernetes environment** is automatically created for the merge request.
    - The environment is linked to a **cloned staging database**.
    - This dynamic environment allows:
        - Developers to run full tests and perform manual testing.
        - QA engineers to test features in isolation and optionally push additional updates.


5. **Approval & Merge:**  
   Once the QA process is complete and the feature is verified:
    - The merge request is approved and merged.
    - Any QA updates (if required) are also merged simultaneously.


6. **Staging Promotion:**  
   The merged branch is deployed to the **shared staging environment**, where full regression tests can be performed, and **Product Owners (POs)** can approve the feature.


7. **Production Release:**  
   After final PO approval and verification on staging, and all E2E by QA are passed successfully, the feature is **deployed to production**.

---

This flow ensures high confidence in every release, simplifies developers and QA communication, and provides clear separation between test environments.
