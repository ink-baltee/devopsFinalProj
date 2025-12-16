pipeline {
    agent any
    
    environment {
        DOCKER_IMAGE = "devops-task-app:\${BUILD_NUMBER}"
    }
    
    stages {
        stage('Fetch Code from GitHub') {
            steps {
                echo "Fetching code from GitHub..."
                checkout scm
                sh 'echo "Files:" && find . -type f | grep -E "Dockerfile|\.php|\.yaml"'
            }
        }
        
        stage('Build Docker Image') {
            steps {
                echo "🐳 Building Docker image..."
                dir('app') {
                    sh "docker build -t \${DOCKER_IMAGE} ."
                }
            }
        }
        
        stage('Push to Docker Hub') {
            steps {
                echo "Skipping Docker Hub for now"
            }
        }
        
        stage('Deploy to Kubernetes') {
            steps {
                echo "Deploying to Kubernetes..."
                
                script {
                    sh 'kubectl apply -f kubernetes/namespace.yaml'
                    sh 'kubectl apply -f kubernetes/mysql-pvc.yaml'
                    sh 'kubectl apply -f kubernetes/mysql-deployment.yaml'
                    sh 'kubectl apply -f kubernetes/mysql-service.yaml'
                    sh 'sleep 15'
                    sh 'kubectl apply -f kubernetes/app-deployment.yaml'
                    sh 'kubectl apply -f kubernetes/app-service.yaml'
                    sh 'kubectl get all -n devops-project'
                }
            }
        }
        
        stage('Setup Monitoring') {
            steps {
                echo "📊 Monitoring setup skipped for now"
            }
        }
    }
    
    post {
        always {
            echo "✅ Pipeline completed!"
        }
    }
}
