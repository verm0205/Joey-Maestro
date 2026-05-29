DROP TABLE IF EXISTS grades;

CREATE TABLE grades (
id          INTEGER PRIMARY KEY AUTO_INCREMENT,
quarter     TEXT    NOT NULL,
course      TEXT    NOT NULL,
ec          REAL    NOT NULL,
toetsing    TEXT    NOT NULL,
cijfer      REAL,
status      INTEGER NOT NULL DEFAULT 0
);

INSERT INTO grades (quarter, course, ec, toetsing, cijfer, status) VALUES
('Quarter 1', 'Program- & Career Orientation', 2.5,  'Portfolio',           9.1,  1),
('Quarter 1', 'Computer Science Basics',       5.0,  'Written Exam',        7.2,  1),
('Quarter 1', 'Programming Basics',            5.0,  'Case Study',          8.5,  1),
('Quarter 2', 'Object Oriented Programming',   10.0, 'Criterium Toets',     NULL, 0),
('Quarter 2', 'Object Oriented Programming',   10.0, 'Project',             NULL, 0),
('Quarter 3', 'Business Processes',            1.25, 'Portfolio',           NULL, 0),
('Quarter 3', 'Computer Science Theory',       1.25, 'Written Exam',        NULL, 0),
('Quarter 3', 'Framework Project 1',           10.0, 'Criterium Toets',     NULL, 0),
('Quarter 3', 'Framework Project 1',           10.0, 'Project',             NULL, 0),
('Quarter 4', 'Framework Project 2',           10.0, 'Presentation (group)',NULL, 0),
('Quarter 4', 'Framework Project 2',           10.0, 'Portfolio',           NULL, 0),
('Quarter 4', 'Framework Project 2',           10.0, 'Portfolio',           NULL, 0);
