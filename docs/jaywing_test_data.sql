USE `jaywing`;

SET FOREIGN_KEY_CHECKS = 0;

-- Insert test data into User table
INSERT INTO `User` (username, email, password, wings, admin) VALUES
('jdoe', 'jdoe@example.com', 'password123', 100, 1),
('asmith', 'asmith@example.com', 'password456', 200, 0),
('bjones', 'bjones@example.com', 'password789', 150, 0);

-- Insert test data into Class table
INSERT INTO `Class` (class_name, description) VALUES
('Math 101', 'Basic Mathematics Class'),
('Physics 101', 'Introduction to Physics'),
('Chemistry 101', 'Introduction to Chemistry');

-- Insert test data into Event_Type table (as it is a required reference for Event table)
INSERT INTO `Event_Type` (type_name, wings) VALUES
('DROP_IN', 100),
('TUTORING', 200),
('GROUP', 300);

-- Insert test data into Event table
INSERT INTO `Event` (event_name, start, end, event_type_id, location) VALUES
('Math Workshop', '2024-11-01 10:00:00', '2024-11-01 12:00:00', 1, 'Room 101'),
('Physics Seminar', '2024-11-02 14:00:00', '2024-11-02 16:00:00', 2, 'Room 102'),
('Chemistry Study Group', '2024-11-03 09:00:00', '2024-11-03 11:00:00', 3, 'Room 103');

-- Insert test data into Chat table
INSERT INTO `Chat` (chat_id, chat_name) VALUES
(1, 'Math 101 Group Chat'),
(2, 'Physics 101 Group Chat'),
(3, 'Chemistry 101 Group Chat');

-- Insert test data into Enrollment table
-- Ensure the user_id and class_id values exist in User and Class tables
INSERT INTO `Enrollment` (class_id, role_in_class, user_id) VALUES
(1, 'Student', 1),
(1, 'Student', 2),
(2, 'Tutor', 3);

-- Insert test data into Attendance table
-- Ensure the user_id and event_id values exist in User and Event tables
INSERT INTO `Attendance` (role_in_event, user_id, event_id) VALUES
('Participant', 1, 1),
('Speaker', 3, 2),
('Organizer', 2, 3);

-- Insert test data into Jobs table
-- Ensure that class_id and admin_id (user_id from User table) exist
INSERT INTO `Jobs` (job_id, class_id, admin_id, description) VALUES
(1, 1, 1, 'Tutor needed for Math 101'),
(2, 2, 1, 'Lab assistant required for Physics 101'),
(3, 3, 1, 'Study group leader for Chemistry 101');

-- Insert test data into Chat_Roster table
-- Ensure user_id and chat_id exist in User and Chat tables
INSERT INTO `Chat_Roster` (chat_roster_id, user_id, chat_id) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 2),
(4, 1, 3);

-- Insert test data into Messages table
-- Ensure chat_id and sender_id (user_id) exist in Chat and User tables
INSERT INTO `Messages` (chat_id, content, sender_id) VALUES
(1, 'Hello everyone!', 1),
(1, 'Hi! Looking forward to the class.', 2),
(2, 'Can anyone help with the assignment?', 3),
(3, 'Let\'s meet before the study group.', 1);

-- Insert test data into Availability table
-- Ensure user_id exists in User table
INSERT INTO `Availability` (user_id, weekday, start_time, end_time) VALUES
(1, 'MONDAY', '09:00:00', '12:00:00'),
(2, 'WEDNESDAY', '13:00:00', '15:00:00'),
(3, 'FRIDAY', '10:00:00', '14:00:00');

-- Insert test data into Rating table
-- Ensure user_id, class_id, and tutor_id (user_id) exist in User and Class tables
INSERT INTO `Rating` (rating_id, user_id, class_id, rating, tutor_id) VALUES
(1, 1, 1, 5, 3),
(2, 2, 1, 4, 3),
(3, 3, 2, 5, 1);

SET FOREIGN_KEY_CHECKS = 1;
