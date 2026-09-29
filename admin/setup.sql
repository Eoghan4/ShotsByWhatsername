-- Run once on the server to add the site_content table
-- mysql -u eoghan -p shots_by_whatsername < /var/www/ShotsByWhatsername/admin/setup.sql

CREATE TABLE IF NOT EXISTS `site_content` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `content_key` VARCHAR(100) NOT NULL UNIQUE,
    `content_value` TEXT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `site_content` (`content_key`, `content_value`) VALUES
('hero_title',       'Shots By Whatsername'),
('hero_subtitle',    'Photography Portfolio'),
('hero_description', 'Welcome to my world in photos! Take a look around!'),
('featured_image_1', ''),
('featured_image_2', ''),
('featured_image_3', ''),
('about_bio',        '<p>Photography has always been a passion of mine - one I inherited from my father. As a child, I was fascinated by my uncle''s darkroom, where he developed his own photographs. That early curiosity inspired me to take an opportunity I was presented with to develop my own photos as a teenager, and I loved every moment of the process.</p><p>In the year 2000, after undergoing spinal cord surgery, it became too painful for me to carry around my camera and lenses. For a long time, I thought my days behind the lens were over. But with the incredible advances in mobile phone cameras, I found a new way to rekindle that passion.</p><p>After joining Instagram and sharing my work, I was delighted to receive encouragement and requests from people who wanted to purchase my photos. Eventually, after selling my work for some time, my son kindly offered to build this website to make the process easier - which brings you here today.</p><p>I hope you enjoy browsing through my gallery as much as I''ve enjoyed capturing each moment.</p><p>Thank you for visiting,</p><p>Dominica</p>'),
('about_photo',      '../pictures/water.webp');
