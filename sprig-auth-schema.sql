CREATE TABLE `roles` (
   `id` int(11) unsigned not null auto_increment,
   `name` varchar(32) not null,
   `description` varchar(255) not null,
   PRIMARY KEY (`id`),
   UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
INSERT INTO `roles` (`id`, `name`, `description`) VALUES 
('1', 'login', 'Login privileges, granted after account confirmation'),
('2', 'admin', 'Administrative user, has access to everything.');
 
CREATE TABLE `roles_users` (
   `user_id` int(10) unsigned not null,
   `role_id` int(10) unsigned not null,
   PRIMARY KEY (`user_id`,`role_id`),
   KEY `fk_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
CREATE TABLE `users` (
   `id` int(11) unsigned not null auto_increment,
   `e-mail` varchar(255) not null,
   `username` varchar(50) not null,
   `password` char(75),
   `autologin` char(40),
   PRIMARY KEY (`id`),
   UNIQUE KEY (`username`),
   UNIQUE KEY (`e-mail`),
   UNIQUE KEY (`autologin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
ALTER TABLE `roles_users`
  ADD CONSTRAINT `roles_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `roles_users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;