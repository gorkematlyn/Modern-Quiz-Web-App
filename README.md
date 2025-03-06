# Quiz Application

A simple and interactive quiz application built with PHP and MySQL. Users can take quizzes in different categories with a time limit and get immediate feedback on their answers.

## Demo

You can try the demo version of this application here: [Quiz App Demo](https://gorkematlyn.github.io/Modern-Quiz-Web-App/)

## Features

- Multiple quiz categories
- Timed quizzes (10 minutes per quiz)
- Immediate feedback on answers
- Progress tracking
- Mobile-friendly interface
- Admin panel for managing questions and categories

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/gorkematlyn/Modern-Quiz-Web-App.git
```

2. Create a MySQL database and import the schema:
```bash
mysql -u your_username -p your_database < database_schema.sql
```

3. Configure the database connection:
   - Open `includes/db.php`
   - Update the database credentials:
     ```php
     $host = "localhost";
     $dbname = "quiz_app";
     $username = "your_username";
     $password = "your_password";
     ```

4. Set up your web server:
   - Point your web server's document root to the project directory
   - Ensure the web server has write permissions for the session directory

5. Access the application:
   - Open your web browser and navigate to the project URL
   - Default admin credentials:
     - Username: admin
     - Password: admin123

## Usage

1. Select a quiz category
2. Answer the questions within the time limit
3. Get immediate feedback on your answers
4. View your final score at the end

## Admin Panel

Access the admin panel at `/admin` to:
- Manage quiz categories
- Add/edit/delete questions
- Import questions via CSV
- View quiz statistics

## Security

- All database queries use prepared statements
- Passwords are hashed using bcrypt
- Direct access to PHP files is prevented
- Session security measures are implemented

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details. 