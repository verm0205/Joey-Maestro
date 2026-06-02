CREATE TABLE profiles (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  about_me TEXT NOT NULL,
  eager_to_learn TEXT,
  perseverance TEXT,
  team_player TEXT,
  languages TEXT,
  github_url VARCHAR(255),
  email VARCHAR(255)
);

INSERT INTO profiles (id, about_me, eager_to_learn, perseverance, team_player, languages, github_url, email)
VALUES (1, 'Ik ben Joey Vermeulen...', 'Ik sta altijd open voor nieuwe technologieën.', 'Ik stop pas als een bug is opgelost.', 'Samenwerken aan code vind ik het leukst.', 'HTML, CSS, JavaScript en PHP', 'github.com/verm0205', 'verm0205@hz.nl');