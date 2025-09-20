#!/bin/bash

# University System - Project Startup Script
# This script helps you start the project easily

echo "🎓 University System - Startup Script"
echo "======================================"

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if PHP is installed
check_php() {
    print_status "Checking PHP installation..."
    if command -v php &> /dev/null; then
        PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2)
        print_success "PHP $PHP_VERSION is installed"
        return 0
    else
        print_error "PHP is not installed"
        return 1
    fi
}

# Check if MySQL is available
check_mysql() {
    print_status "Checking MySQL connection..."
    if command -v mysql &> /dev/null; then
        print_success "MySQL client is available"
        return 0
    else
        print_warning "MySQL client not found, but Docker MySQL might be available"
        return 1
    fi
}

# Install dependencies (macOS with Homebrew)
install_dependencies_macos() {
    print_status "Installing dependencies for macOS..."
    
    # Check if Homebrew is installed
    if ! command -v brew &> /dev/null; then
        print_status "Installing Homebrew..."
        /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    fi
    
    # Install PHP
    print_status "Installing PHP..."
    brew install php
    
    # Install MySQL
    print_status "Installing MySQL..."
    brew install mysql
    
    # Start MySQL service
    print_status "Starting MySQL service..."
    brew services start mysql
    
    print_success "Dependencies installed successfully!"
}


# Start with PHP built-in server
start_with_php() {
    print_status "Starting project with PHP built-in server..."
    
    # Check if Database.php needs configuration
    if grep -q "private const PASSWORD = '';" Database.php; then
        print_warning "Database password is empty. You may need to configure Database.php"
    fi
    
    # Test database connection
    print_status "Testing database connection..."
    php Database.php
    
    if [ $? -eq 0 ]; then
        print_success "Database connection successful!"
    else
        print_warning "Database connection failed. Please check your MySQL configuration."
    fi
    
    # Start PHP server
    print_status "Starting PHP development server on http://localhost:8000"
    print_status "Press Ctrl+C to stop the server"
    php -S localhost:8000
}

# Main menu
show_menu() {
    echo ""
    echo "Choose how to start the project:"
    echo "1) PHP Built-in server (requires PHP + MySQL)"
    echo "2) Install dependencies for macOS"
    echo "3) Install dependencies for Ubuntu/Debian"
    echo "4) Exit"
    echo ""
}

# Install dependencies for Ubuntu/Debian
install_dependencies_ubuntu() {
    print_status "Installing dependencies for Ubuntu/Debian..."
    
    # Update package list
    sudo apt update
    
    # Install PHP and extensions
    print_status "Installing PHP and extensions..."
    sudo apt install -y php php-mysql php-pdo php-mbstring php-json php-curl
    
    # Install MySQL
    print_status "Installing MySQL..."
    sudo apt install -y mysql-server
    
    # Start and enable MySQL
    print_status "Starting MySQL service..."
    sudo systemctl start mysql
    sudo systemctl enable mysql
    
    print_success "Dependencies installed successfully!"
    print_status "You may need to run 'sudo mysql_secure_installation' to secure MySQL"
}

# Check current OS
detect_os() {
    if [[ "$OSTYPE" == "darwin"* ]]; then
        echo "macos"
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        if [ -f /etc/debian_version ]; then
            echo "debian"
        elif [ -f /etc/redhat-release ]; then
            echo "redhat"
        else
            echo "linux"
        fi
    else
        echo "unknown"
    fi
}

# Main script logic
main() {
    # Detect OS
    OS=$(detect_os)
    print_status "Detected OS: $OS"
    
    while true; do
        show_menu
        read -p "Enter your choice [1-4]: " choice
        
        case $choice in
            1)
                if check_php; then
                    start_with_php
                else
                    print_error "PHP is required for this option. Please install PHP first using option 2 or 3."
                fi
                break
                ;;
            2)
                if [[ "$OS" == "macos" ]]; then
                    install_dependencies_macos
                else
                    print_warning "This option is for macOS only. Use option 3 for Ubuntu/Debian."
                fi
                ;;
            3)
                if [[ "$OS" == "debian" ]]; then
                    install_dependencies_ubuntu
                else
                    print_warning "This option is for Ubuntu/Debian only. Use option 2 for macOS."
                fi
                ;;
            4)
                print_status "Goodbye!"
                exit 0
                ;;
            *)
                print_error "Invalid option. Please choose 1-4."
                ;;
        esac
    done
}

# Run main function
main
