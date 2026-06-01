CREATE TABLE posts (
       id         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
       title      VARCHAR(255) NOT NULL,
       path       VARCHAR(255) NOT NULL UNIQUE,
       body       TEXT         NOT NULL,
       status     ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
       created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
       updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO posts (title, path, body, status, created_at, updated_at) VALUES
      (
          'My First Week at HZ University',
          'my-first-week-at-hz-university',
          'Starting university was both exciting and nerve-wracking. The first week was packed with introductions, campus tours, and getting to know my fellow students. I quickly discovered that the IT programme here has a strong focus on hands-on projects, which suits my learning style perfectly.',
          'published',
          NOW(),
          NOW()
      ),
      (
          'What I Learned Building My First PHP Framework',
          'what-i-learned-building-my-first-php-framework',
          'This quarter we built a mini MVC framework from scratch using PHP 8. The experience taught me a lot about routing, dependency injection, and clean architecture. Understanding how frameworks work under the hood has made me a much better developer.',
          'published',
          NOW(),
          NOW()
      ),
      (
          'SWOT Analysis: My Strengths as a Developer',
          'swot-analysis-my-strengths-as-a-developer',
          'As part of my Programme & Career Orientation course, I conducted a SWOT analysis on myself. My key strengths include problem-solving, attention to detail, and quick learning. My main weakness is sometimes over-engineering solutions. This is still a draft and will be published soon.',
          'draft',
          NOW(),
          NOW()
      );