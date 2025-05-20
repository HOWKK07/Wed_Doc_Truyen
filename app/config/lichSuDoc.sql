CREATE TABLE IF NOT EXISTS `lich_su_doc` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_user` int(11) NOT NULL,
    `id_truyen` int(11) NOT NULL,
    `chuong_doc` int(11) NOT NULL,
    `ngay_doc` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `id_user` (`id_user`),
    KEY `id_truyen` (`id_truyen`),
    CONSTRAINT `lich_su_doc_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `lich_su_doc_ibfk_2` FOREIGN KEY (`id_truyen`) REFERENCES `truyen` (`id_truyen`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 