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
                sh 'vendor/bin/phpunit --configuration tests/phpunit.xml'
            }
        }

        // Add more stages as needed
    }

}
