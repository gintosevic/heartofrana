
CREATE TABLE Race (
                race_id INT AUTO_INCREMENT NOT NULL,
                growth TINYINT DEFAULT 0 NOT NULL,
                science TINYINT DEFAULT 0 NOT NULL,
                culture TINYINT DEFAULT 0 NOT NULL,
                production TINYINT DEFAULT 0 NOT NULL,
                speed TINYINT DEFAULT 0 NOT NULL,
                attack TINYINT DEFAULT 0 NOT NULL,
                defense TINYINT DEFAULT 0 NOT NULL,
                PRIMARY KEY (race_id)
);


CREATE UNIQUE INDEX race_idx
 ON Race
 ( growth, science, culture, production, speed, attack, defense );

CREATE TABLE Account (
                login VARCHAR(20) NOT NULL,
                password_hash VARCHAR(128) NOT NULL,
                email VARCHAR(50) NOT NULL,
                PRIMARY KEY (login)
);


CREATE TABLE Galaxy (
                galaxy_id TINYINT NOT NULL,
                spiral INT NOT NULL,
                science_start INT DEFAULT 0 NOT NULL,
                production_start INT DEFAULT 0 NOT NULL,
                PRIMARY KEY (galaxy_id)
);


CREATE TABLE Alliance (
                tag VARCHAR(5) NOT NULL,
                name VARCHAR(128) NOT NULL,
                PRIMARY KEY (tag)
);


CREATE TABLE Player (
                player_id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(20) NOT NULL,
                race_id INT NOT NULL,
                points TINYINT DEFAULT 0 NOT NULL,
                rank INT DEFAULT 0 NOT NULL,
                experience INT DEFAULT 0 NOT NULL,
                culture INT DEFAULT 0 NOT NULL,
                current_science VARCHAR(20) NOT NULL,
                tag VARCHAR(5),
                PRIMARY KEY (player_id)
);


CREATE TABLE Science (
                player_id INT NOT NULL,
                biology INT DEFAULT 0 NOT NULL,
                economy INT DEFAULT 0 NOT NULL,
                energy INT DEFAULT 0 NOT NULL,
                mathematics INT DEFAULT 0 NOT NULL,
                physics INT DEFAULT 0 NOT NULL,
                social INT DEFAULT 0 NOT NULL,
                PRIMARY KEY (player_id)
);


CREATE TABLE Event (
                event_id BIGINT AUTO_INCREMENT NOT NULL,
                player_id INT,
                time DATETIME NOT NULL,
                type VARCHAR(20) NOT NULL,
                title VARCHAR(128) NOT NULL,
                text VARCHAR(1024) NOT NULL,
                new BOOLEAN DEFAULT 1 NOT NULL,
                PRIMARY KEY (event_id)
);


CREATE TABLE Nap (
                player_a INT NOT NULL,
                player_b INT NOT NULL,
                alliance_a VARCHAR(5) NOT NULL,
                alliance_b VARCHAR(5) NOT NULL,
                PRIMARY KEY (player_a, player_b, alliance_a, alliance_b)
);


CREATE TABLE Fleet (
                fleet_id INT AUTO_INCREMENT NOT NULL,
                owner INT NOT NULL,
                colonyships INT NOT NULL,
                transports INT NOT NULL,
                destroyers INT NOT NULL,
                cruisers INT NOT NULL,
                battleships INT NOT NULL,
                PRIMARY KEY (fleet_id)
);


CREATE TABLE System (
                sid INT AUTO_INCREMENT NOT NULL,
                y INT NOT NULL,
                x INT NOT NULL,
                name VARCHAR(30) NOT NULL,
                n_homes TINYINT DEFAULT 0 NOT NULL,
                PRIMARY KEY (sid)
);


CREATE UNIQUE INDEX system_unique_coordinates
 ON System
 ( y ASC, x ASC );

CREATE TABLE Planet (
                sid INT NOT NULL,
                position TINYINT NOT NULL,
                bonus BOOLEAN DEFAULT false NOT NULL,
                population TINYINT DEFAULT 1 NOT NULL,
                farm INT DEFAULT 0 NOT NULL,
                factory INT DEFAULT 0 NOT NULL,
                cybernet INT DEFAULT 0 NOT NULL,
                lab INT DEFAULT 0 NOT NULL,
                starbase INT DEFAULT 0 NOT NULL,
                production INT DEFAULT 0 NOT NULL,
                owner INT,
                owner_fleet INT,
                sieging_fleet INT,
                PRIMARY KEY (sid, position)
);


CREATE TABLE Flight (
                fleet_id INT NOT NULL,
                departure_time DATETIME NOT NULL,
                departure_sid INT NOT NULL,
                departure_position TINYINT NOT NULL,
                arrival_time DATETIME NOT NULL,
                arrival_sid INT NOT NULL,
                arrival_position TINYINT NOT NULL,
                PRIMARY KEY (fleet_id)
);


ALTER TABLE Player ADD CONSTRAINT race_player_fk
FOREIGN KEY (race_id)
REFERENCES Race (race_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Player ADD CONSTRAINT account_player_fk
FOREIGN KEY (name)
REFERENCES Account (login)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Player ADD CONSTRAINT alliance_player_fk
FOREIGN KEY (tag)
REFERENCES Alliance (tag)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Nap ADD CONSTRAINT alliance_nap_fk
FOREIGN KEY (alliance_a)
REFERENCES Alliance (tag)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Nap ADD CONSTRAINT alliance_nap_fk1
FOREIGN KEY (alliance_b)
REFERENCES Alliance (tag)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Planet ADD CONSTRAINT player_planet_fk
FOREIGN KEY (owner)
REFERENCES Player (player_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Fleet ADD CONSTRAINT player_fleet_fk
FOREIGN KEY (owner)
REFERENCES Player (player_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Nap ADD CONSTRAINT player_nap_fk
FOREIGN KEY (player_a)
REFERENCES Player (player_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Nap ADD CONSTRAINT player_nap_fk1
FOREIGN KEY (player_b)
REFERENCES Player (player_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Event ADD CONSTRAINT player_event_fk
FOREIGN KEY (player_id)
REFERENCES Player (player_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Science ADD CONSTRAINT player_science_fk
FOREIGN KEY (player_id)
REFERENCES Player (player_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Flight ADD CONSTRAINT fleet_flight_fk
FOREIGN KEY (fleet_id)
REFERENCES Fleet (fleet_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Planet ADD CONSTRAINT fleet_planet_fk
FOREIGN KEY (owner_fleet)
REFERENCES Fleet (fleet_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Planet ADD CONSTRAINT fleet_planet_fk1
FOREIGN KEY (sieging_fleet)
REFERENCES Fleet (fleet_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Planet ADD CONSTRAINT is_in
FOREIGN KEY (sid)
REFERENCES System (sid)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Flight ADD CONSTRAINT planet_flight_fk
FOREIGN KEY (arrival_sid, arrival_position)
REFERENCES Planet (sid, position)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE Flight ADD CONSTRAINT planet_flight_fk1
FOREIGN KEY (departure_sid, departure_position)
REFERENCES Planet (sid, position)
ON DELETE NO ACTION
ON UPDATE NO ACTION;
