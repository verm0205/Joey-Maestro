REATE TABLE tasks (
                       id INTEGER PRIMARY KEY AUTO_INCREMENT,
                       title TEXT,
                       description TEXT,
                       priority INTEGER,
                       status INTEGER,
                       progress INTEGER,
                       created_at INTEGER,
                       completed_at INTEGER
);
INSERT INTO tasks (title, description, priority, status, progress, created_at, completed_at) VALUES
                                                                                                 ('Form the Fellowship', 'Assemble representatives of the Free Peoples in Rivendell', 1, 4, 100, UNIX_TIMESTAMP(NOW() - INTERVAL 3 DAY), UNIX_TIMESTAMP(NOW())),
                                                                                                 ('Cross the Misty Mountains', 'Find a safe passage through or around the mountains', 2, 4, 100, UNIX_TIMESTAMP(NOW() - INTERVAL 2 DAY), UNIX_TIMESTAMP(NOW())),
                                                                                                 ('Enter Moria', 'Take the risky path through the Mines of Moria', 3, 1, 70, UNIX_TIMESTAMP(NOW()), NULL),
                                                                                                 ('Battle the Balrog', 'Gandalf confronts the Balrog to protect the fellowship', 1, 2, 50, UNIX_TIMESTAMP(NOW()), NULL),
                                                                                                 ('Reach Lothlórien', 'Find refuge and counsel with Galadriel', 2, 2, 50, UNIX_TIMESTAMP(NOW()), NULL),
                                                                                                 ('Track the Uruk-hai', 'Aragorn, Legolas and Gimli pursue the captured hobbits', 2, 2, 50, UNIX_TIMESTAMP(NOW()), NULL),
                                                                                                 ('Entmoot', 'Merry and Pippin persuade the Ents to fight Saruman', 3, 2, 50, UNIX_TIMESTAMP(NOW()), NULL),
                                                                                                 ('Siege of Helm''s Deep', 'Defend the fortress from Saruman''s army', 1, 2, 50, UNIX_TIMESTAMP(NOW()), NULL),
                                                                                                 ('Gandalf returns', 'Gandalf the White returns to aid Rohan', 1, 2, 50, UNIX_TIMESTAMP(NOW()), NULL),
                                                                                                 ('Defeat Saruman', 'Confront Saruman at Isengard after his defeat', 2, 2, 50, UNIX_TIMESTAMP(NOW()), NULL),
                                                                                                 ('Journey to Minas Tirith', 'Prepare for the battle against Sauron''s forces', 2, 1, 50, UNIX_TIMESTAMP(NOW()), NULL),
                                                                                                 ('Battle of Pelennor Fields', 'Defend the city from the forces of Mordor', 1, 2, 50, UNIX_TIMESTAMP(NOW()), NULL),
                                                                                                 ('The Paths of the Dead', 'Aragorn seeks aid from the Dead Men of Dunharrow', 2, 2, 50, UNIX_TIMESTAMP(NOW()), NULL),
                                                                                                 ('Distract Sauron', 'March on the Black Gate to draw Sauron''s gaze', 3, 2, 50, UNIX_TIMESTAMP(NOW()), NULL),
                                                                                                 ('Destroy the Ring', 'Frodo and Sam reach Mount Doom and destroy the Ring', 1, 2, 50, UNIX_TIMESTAMP(NOW()), NULL);