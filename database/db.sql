CREATE TABLE `video_uploads` (
 `postId` int(11) NOT NULL AUTO_INCREMENT,
 `channelId` varchar(30) NOT NULL,
 `videoId` varchar(255) NOT NULL,
 `publishedAt` varchar(255) NOT NULL,
 `live` varchar(30) NOT NULL,
 `title` varchar(255),
 KEY `videoId` (`videoId`),
 KEY `title` (`live`),
 KEY `publishedAt` (`publishedAt`),
 PRIMARY KEY (`postId`)
);