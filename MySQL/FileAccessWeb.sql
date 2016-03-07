CREATE DATABASE FileAccessWeb;

USE FileAccessWeb;

CREATE TABLE tblUsers
    (
    pk bigint not null auto_increment primary key,
    FirstName varchar(64),
    LastName varchar(64),
    UserID varchar(64),
    Password varchar(256),
    Salt varchar(256),
    UserType smallint,
    UserExpires date,
    DateStamp date
    );

CREATE TABLE tblLogonLog
    (
    pk bigint not null auto_increment primary key,
    UserID varchar(64),
    FailedLogon smallint,
    TimeAttempt datetime,
    IPAddress varchar(64),
    DateStamp date
    );

CREATE TABLE tblFiles
    (
    pk bigint not null auto_increment primary key,
    FileHash varchar(256),
    FileSize varchar(32),
    FilePath varchar(256),
    FileCreation date,
    DateStamp date
    );

CREATE TABLE tblTags
    (
    pk bigint not null auto_increment primary key,
    Tag varchar(64)
    );

CREATE TABLE lutblTags
    (
    pk bigint not null auto_increment primary key,
    TagPK bigint,
    FilePK bigint
    );

CREATE TABLE tblRating
    (
    pk bigint not null auto_increment primary key,
    Rating int,
    FilePK bigint,
    DateStamp date
    );

CREATE TABLE tblType
    (
    pk bigint not null auto_increment primary key,
    FileType varchar(64)
    );

CREATE TABLE lutblType
    (
    pk bigint not null auto_increment primary key,
    TypePK bigint,
    FilePK bigint
    );

CREATE USER 'ReadUser'@'localhost' IDENTIFIED BY 'Test123';
GRANT SELECT ON FileAccessWeb.* TO 'ReadUser'@'localhost';

CREATE USER 'WebAdmin'@'localhost' IDENTIFIED BY 'Test123';
GRANT INSERT, UPDATE, SELECT, DELETE ON FileAccessWeb.tblUsers to 'WebAdmin'@'localhost';

CREATE USER 'WriteUser'@'localhost' IDENTIFIED BY 'Test123';
GRANT INSERT ON FileAccessWeb.tblLogonLog TO 'WriteUser'@'localhost';
GRANT INSERT ON FileAccessWeb.tblFiles TO 'WriteUser'@'localhost';
GRANT INSERT ON FileAccessWeb.tblTags TO 'WriteUser'@'localhost';
GRANT INSERT ON FileAccessWeb.lutblTags TO 'WriteUser'@'localhost';
GRANT INSERT ON FileAccessWeb.tblRating TO 'WriteUser'@'localhost';
GRANT INSERT ON FileAccessWeb.tblType TO 'WriteUser'@'localhost';
GRANT INSERT ON FileAccessWeb.lutblType TO 'WriteUser'@'localhost';
