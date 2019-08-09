-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2019 at 03:31 AM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 7.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bmcc`
--

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `CourseNo` char(6) NOT NULL,
  `Title` varchar(30) DEFAULT NULL,
  `Credits` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`CourseNo`, `Title`, `Credits`) VALUES
('CIS100', 'Comp. Applications', 3),
('CIS115', 'Intro.Comp.Inf.Secu', 3),
('CIS120', 'Introduction to Data Base Applications', 2),
('CIS140', 'Introduction to Spreadsheet Applications', 2),
('CIS155', 'Computer Hardware', 4),
('CIS160', 'Desktop Publishing Packages', 2),
('CIS165', 'Introduction to Operating Systems', 3),
('CIS180', 'Intro To Internet', 3),
('CIS200', 'Intro.Info System', 3),
('CIS207', 'Healthcare Information Tech. and Management Sys.', 4),
('CIS220', 'Visual BASIC', 3),
('CIS235', 'Computer Operations I', 4),
('CIS255', 'Computer Software', 4),
('CIS280', 'Advanced Internet Applications', 3),
('CIS316', 'Introduction to Digital Forensics', 3),
('CIS317', 'Introduction to Cyptography', 4),
('CIS325', 'System Analysis', 3),
('CIS335', 'Computer Operations II//JCL', 3),
('CIS345', 'Telecom. Networks I', 4),
('CIS359', 'Information Assurance', 3),
('CIS362', 'Cloud Computing', 3),
('CIS364', 'Mobile Device Programming', 3),
('CIS385', 'Web Programming I', 3),
('CIS390', 'Wireless Programming ', 3),
('CIS395', 'Database Systems I', 4),
('CIS420', 'System Implementation', 3),
('CIS440', 'Unix', 3),
('CIS445', 'Tele. Networks II', 4),
('CIS455', 'Network Security', 4),
('CIS459', 'Ethical Hacking & System Defense', 3),
('CIS465', 'Business Systems II', 3),
('CIS475', 'Wiresless Information Networks', 4),
('CIS480', 'Operating Systems Concepts', 3),
('CIS485', 'Web Programming II', 3),
('CIS490', 'Introduction to Data Science', 3),
('CIS495', 'Database Systems II', 3),
('CSC101', 'Principles In Info Tech & Comp', 3),
('CSC110', 'Comp. Programming I', 4),
('CSC111', 'Introduction to Programming', 4),
('CSC210', 'Comp. Prog. II', 4),
('CSC211', 'Advanced Programming Technique', 3),
('CSC215', 'Fund. of Computer Systems', 3),
('CSC230', 'Discrete Structures', 3),
('CSC231', 'Disc Struc & Applic Comp Sci', 3),
('CSC310', 'Assem Lang & Arc I', 3),
('CSC330', 'Data Structures I', 3),
('CSC331', 'Data Structures', 3),
('CSC350', 'Software Development', 3),
('CSC410', 'Assem Lang & Arc II', 3),
('CSC430', 'Data Structures II', 3),
('CSC450', 'Computer Graphics', 3),
('CSC470', 'Mathematical Foundations of Computer', 4),
('GIS101', 'Digital Earth', 3),
('GIS201', 'Intro To Geographic Methods', 3),
('GIS261', 'Intro To Geographic Info Sci', 3),
('GIS325', 'GIS Internship', 2),
('GIS361', 'Advanced Geographic Info Sci', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`CourseNo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
