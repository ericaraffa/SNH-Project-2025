USE snh;

CREATE TABLE `user`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(200) NOT NULL,
    `username` VARCHAR(200) NOT NULL,
    `password` CHAR(64) NOT NULL,
    `verified` BOOLEAN DEFAULT FALSE,
    `locked` BOOLEAN DEFAULT FALSE,
    `premium` BOOLEAN DEFAULT FALSE,
    `admin` BOOLEAN DEFAULT FALSE,

    PRIMARY KEY (`id`)
) CHARACTER SET=utf8mb4;

CREATE TABLE `user_verification`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token` CHAR(64) NOT NULL,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
) CHARACTER SET=utf8mb4;

CREATE TABLE `user_recover`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token` CHAR(64) NOT NULL,
    `valid_until` DATETIME NOT NULL,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
) CHARACTER SET=utf8mb4;

CREATE TABLE `session`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token` CHAR(64) NOT NULL,
    `valid_until` DATETIME NOT NULL,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
) CHARACTER SET=utf8mb4;

CREATE TABLE `novel`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `text` TEXT DEFAULT NULL,
    `premium` BOOLEAN DEFAULT FALSE,

    PRIMARY KEY (`id`)
) CHARACTER SET=utf8mb4;

CREATE TABLE `wrong_login`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
) CHARACTER SET=utf8mb4;

CREATE TABLE `user_lock`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token` CHAR(64) NOT NULL,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
) CHARACTER SET=utf8mb4;

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;


# Initialize admin user
INSERT INTO user VALUES
    (DEFAULT, 'admin@admin.com', 'admin', '$2y$10$OWB6J1BR4oO/NXnTJI3k6u15PiyuXUbGxApWuXCBkDVSXk.Ezcm8.', TRUE, FALSE, TRUE, TRUE);

INSERT INTO user VALUES
    (DEFAULT, 'email', 'debug', '$2y$10$o8aM1dN03jOtCRzgzw2jUOr/AeMmBQEgqkwkqUff.BSNfo8fOR08O', TRUE, FALSE, FALSE, FALSE);


INSERT INTO novel VALUES 
    (DEFAULT, "Claudione", "OOOOOOOOOOOOOOOOOOOOOOOOOOOOOO
NONOSTANTE NELLA MIA CREDENZA SI NOTI L'ASSANZA DI ZUCCHERO E THEEEEEE
SONO ANCORA IL SIGNORE DEL MONDO PIu' RICCO CHE C'e'
IO
NONOSTANTE NEL MIO GUARDAROBA DI TUTTA LA ROBA E' RIMASTO UN GILEEEET
SONO ANCORA IL SIGORE DEL MONDO PIU' RICCO CHE C'E'", FALSE), 
    (DEFAULT, "Claudione PREMIUM", "Plenty of Lasagna, Today se Magna", TRUE);

