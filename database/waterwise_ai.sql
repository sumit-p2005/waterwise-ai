CREATE DATABASE IF NOT EXISTS waterwise_ai;
USE waterwise_ai;

CREATE TABLE IF NOT EXISTS water_data (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ph FLOAT NOT NULL,
  tds FLOAT NOT NULL,
  do_level FLOAT NOT NULL,
  turbidity FLOAT NOT NULL,
  temperature FLOAT NOT NULL,
  bio_chemical_oxygen_demand FLOAT NOT NULL DEFAULT 0,
  faecal_streptococci FLOAT NOT NULL DEFAULT 0,
  nitrate FLOAT NOT NULL DEFAULT 0,
  faecal_coliform FLOAT NOT NULL DEFAULT 0,
  total_coliform FLOAT NOT NULL DEFAULT 0,
  conductivity FLOAT NOT NULL DEFAULT 0,
  wqi FLOAT NOT NULL,
  latitude FLOAT NULL,
  longitude FLOAT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dataset_data (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ph FLOAT NOT NULL,
  tds FLOAT NOT NULL,
  do_level FLOAT NOT NULL,
  turbidity FLOAT NOT NULL,
  temperature FLOAT NOT NULL,
  bio_chemical_oxygen_demand FLOAT NOT NULL DEFAULT 0,
  faecal_streptococci FLOAT NOT NULL DEFAULT 0,
  nitrate FLOAT NOT NULL DEFAULT 0,
  faecal_coliform FLOAT NOT NULL DEFAULT 0,
  total_coliform FLOAT NOT NULL DEFAULT 0,
  conductivity FLOAT NOT NULL DEFAULT 0,
  wqi FLOAT NOT NULL
);

INSERT INTO water_data (ph, tds, do_level, turbidity, temperature, bio_chemical_oxygen_demand, faecal_streptococci, nitrate, faecal_coliform, total_coliform, conductivity, wqi, latitude, longitude) VALUES
(7.2, 145, 8.4, 1.1, 24, 1.1, 120, 2.4, 60, 350, 145, 36.24, 28.6139, 77.2090),
(6.8, 320, 7.1, 3.3, 27, 3.3, 420, 6.8, 220, 960, 320, 82.65, 19.0760, 72.8777),
(7.9, 590, 5.5, 7.4, 30, 7.4, 700, 15.2, 430, 1800, 590, 168.12, 13.0827, 80.2707);
