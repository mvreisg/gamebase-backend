CREATE DATABASE gamebase;
USE gamebase;

CREATE TABLE user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
);

CREATE TABLE game (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(400) NOT NULL UNIQUE
);

CREATE TABLE platform (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE genre (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE game_genre (
    id INT PRIMARY KEY AUTO_INCREMENT,
    genre_id INT NOT NULL,
    game_id INT NOT NULL,
    FOREIGN KEY (genre_id) REFERENCES genre(id),
    FOREIGN KEY (game_id) REFERENCES game(id)
);

CREATE TABLE game_platform (
    id INT PRIMARY KEY AUTO_INCREMENT,
    platform_id INT NOT NULL,
    game_id INT NOT NULL,
    FOREIGN KEY (platform_id) REFERENCES platform(id),
    FOREIGN KEY (game_id) REFERENCES game(id)
);