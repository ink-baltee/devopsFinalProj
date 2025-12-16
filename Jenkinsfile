pipeline {
    agent any
    
    environment {
        DOCKER_IMAGE = "yourdockerhubusername/devops-task-app:${BUILD_NUMBER}"
    }
    
    stages {
        // Stage 1: Code Fetch State [6 marks]
        stage('Fetch Code from GitHub') {
            steps {
                echo "📥 Fetching code from GitHub..."
                checkout scm
                sh 'echo "Files fetched:" && ls -la'
            }
        }
        
        // Stage 2: Docker Image Creation Stage [10 marks]
        stage('Build Docker Image') {
            steps {
                echo "🐳 Building Docker image..."
                dir('app') {
                    sh "docker build -t ${DOCKER_IMAGE} ."
                }
            }
        }
        
        stage('Push to Docker Hub') {
            steps {
                echo "📤 Pushing to Docker Hub..."
                withCredentials([usernamePassword(
                    credentialsId: 'docker-hub-creds',
                    usernameVariable: 'DOCKER_USER',
                    passwordVariable: 'DOCKER_PASS'
                )]) {
                    sh """
                    docker login -u $DOCKER_USER -p $DOCKER_PASS
                    docker push ${DOCKER_IMAGE}
                    """
                }
            }
        }
        
        // Stage 3: Kubernetes Deployment Stage [17 marks]
        stage('Deploy to Kubernetes') {
            steps {
                echo "🚀 Deploying to Kubernetes..."
                
                // Create namespace
                sh 'kubectl apply -f kubernetes/namespace.yaml'
                
                // Deploy MySQL
                sh '''
                kubectl apply -f kubernetes/mysql-pvc.yaml
                kubectl apply -f kubernetes/mysql-deployment.yaml
                kubectl apply -f kubernetes/mysql-service.yaml
                '''
                
                // Wait for MySQL
                sh 'kubectl wait --for=condition=ready pod -l app=mysql -n devops-project --timeout=120s'
                
                // Initialize database
                sh 'kubectl apply -f kubernetes/db-init-job.yaml'
                
                // Update app deployment with new image
                sh """
                sed -i 's|image:.*|image: ${DOCKER_IMAGE}|g' kubernetes/app-deployment.yaml
                kubectl apply -f kubernetes/app-deployment.yaml
                kubectl apply -f kubernetes/app-service.yaml
                """
                
                // Check deployment
                sh 'kubectl get all -n devops-project'
            }
        }
        
        // Stage 4: Prometheus/Grafana Stage [17 marks]
        stage('Setup Monitoring') {
            steps {
                echo "📊 Setting up monitoring..."
                
                // Install Prometheus stack if not exists
                sh '''
                helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
                helm repo update
                helm upgrade --install prometheus prometheus-community/kube-prometheus-stack \
                    --namespace monitoring \
                    --create-namespace \
                    --set grafana.adminPassword="admin123"
                '''
                
                // Apply ServiceMonitor
                sh 'kubectl apply -f monitoring/servicemonitor.yaml'
                
                // Get URLs
                sh '''
                echo "=== Monitoring URLs ==="
                echo "Grafana: http://<EC2-IP>:3000"
                echo "Prometheus: http://<EC2-IP>:9090"
                echo "Application: http://<EC2-IP>:30001"
                '''
            }
        }
    }
    
    post {
        always {
            echo "✅ Pipeline completed!"
            sh 'docker system prune -f'
        }
    }
}
