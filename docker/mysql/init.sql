CREATE TABLE IF NOT EXISTS users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS pomodoro_session (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    task_description TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_settings (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    work_duration INT DEFAULT 25,  
    short_break_duration INT DEFAULT 5,
    long_break_duration INT DEFAULT 15,
    break_interval INT DEFAULT 4,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (username) VALUES 
('john'),
('joe'),
('bob');

INSERT INTO user_settings (user_id, work_duration, short_break_duration, long_break_duration, break_interval) VALUES
(1, 25, 5, 15, 4),
(2, 30, 5, 20, 4),
(3, 40, 10, 30, 5);