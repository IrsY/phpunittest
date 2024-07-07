pipeline {
    agent {
        docker {
            image 'composer:latest'
        }
    }

    stages {

        stage('Install Dependencies') {
            steps {
                // Install Composer dependencies
                sh 'composer install'
            }
        }

        stage('Run PHPUnit Tests') {
            steps {
                // Run PHPUnit tests
                sh 'vendor/bin/phpunit --log-junit logs/unitreport.xml --configuration src/tests/phpunit.xml'
            }
        }

        // Add more stages as needed
    }

    post {
		always {
			junit testResults: 'logs/unitreport.xml'
		}
	}

}
