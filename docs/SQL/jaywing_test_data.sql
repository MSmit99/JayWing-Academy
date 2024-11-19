USE `jaywing`;

SET FOREIGN_KEY_CHECKS = 0;

-- Insert test data into User table - THIS NEEDS TO BE DONE ON THE WEBSITE SO THAT THE HASHING WORKS
-- INSERT INTO `User` (username, firstName, lastName, email, password, wings, admin, publicProfile) VALUES
-- ('techtutor', 'Alex', 'Rodriguez', 'alex.rod@jaywing.edu', 'hashedpassword123', 500, 0, 1),
-- ('mathmagician', 'Emma', 'Chen', 'emma.chen@jaywing.edu', 'securepass456', 750, 1, 1),
-- ('sciencewhiz', 'Liam', 'Patel', 'liam.patel@jaywing.edu', 'protectedkey789', 600, 0, 0),
-- ('artcreator', 'Sofia', 'Martinez', 'sofia.m@jaywing.edu', 'creativepath101', 400, 0, 1);

-- Insert dummy Classes
INSERT INTO `Class` (className, courseCode, classDescription) VALUES
('Machine Learning Basics', 'CS301', 'Introduction to machine learning algorithms and techniques'),
('Modern Art History', 'ART205', 'Exploring art movements from 20th century to present'),
('Advanced Calculus', 'MA302', 'Deep dive into multivariable calculus and complex analysis'),
('Web Development', 'CS250', 'Full-stack web development with modern frameworks');

-- Insert dummy Enrollments
INSERT INTO `Enrollment` (class_id, user_id, roleOfClass, roleDescription) VALUES
(1, 1, 'Tutor', 'Assisting students with programming concepts'),
(1, 3, 'Student', 'Learning machine learning fundamentals'),
(2, 4, 'Student', 'Exploring contemporary art theories'),
(3, 2, 'Tutor', 'Guiding students through advanced mathematical concepts'),
(4, 1, 'Student', 'Learning full-stack web development techniques');

-- Insert dummy Event Types
INSERT INTO `Event_Type` (eventTypeName, wings) VALUES
('WORKSHOP', 250),
('NETWORKING', 150),
('CONFERENCE', 400);

-- Insert dummy Events
INSERT INTO `Event` (type_id, eventName, eventStartTime, eventEndTime, location, eventDescription) VALUES
(1, 'AI Ethics Workshop', '2024-12-05 09:00:00', '2024-12-05 13:00:00', 'Tech Hall A', 'Exploring ethical considerations in artificial intelligence'),
(2, 'Tech Startup Mixer', '2024-12-12 18:00:00', '2024-12-12 21:00:00', 'Innovation Center', 'Networking event for tech entrepreneurs and innovators'),
(3, 'Annual Computer Science Conference', '2025-01-15 10:00:00', '2025-01-17 17:00:00', 'Convention Center', 'Multi-day conference featuring research presentations and keynote speakers');

-- Insert dummy Attendance
INSERT INTO `Attendance` (user_id, event_id, roleOfEvent, isCreator) VALUES
(1, 1, 'Presenter', 1),
(2, 2, 'Participant', 0),
(3, 3, 'Speaker', 0),
(4, 1, 'Attendee', 0);

-- Insert dummy Chats
INSERT INTO `Chat` (chat_id, chatName, chatDescription) VALUES
(1, 'ML Study Group', 'Discussion forum for machine learning students'),
(2, 'Web Dev Collaboration', 'Collaborative space for web development projects'),
(3, 'Art History Discussions', 'Platform for sharing insights on art history');

-- Insert dummy Chat Participants
INSERT INTO `Chat_Participant` (participant_id, chat_id, user_id, joinedAt) VALUES
(1, 1, 1, '2024-11-20 10:30:00'),
(2, 1, 3, '2024-11-21 14:45:00'),
(3, 2, 1, '2024-11-22 09:15:00'),
(4, 3, 4, '2024-11-23 16:20:00');

-- Insert dummy Messages
INSERT INTO `Messages` (chat_id, sender_id, messageContent) VALUES
(1, 1, 'Welcome to the Machine Learning study group! Let''s discuss our current project.'),
(1, 3, 'Hi everyone! Can someone explain gradient descent?'),
(2, 1, 'I''ve found a great tutorial on React hooks. Check it out!'),
(3, 4, 'Thoughts on the latest modern art exhibition?');

-- Insert dummy Availability
INSERT INTO `Availability` (user_id, weekday, start, end) VALUES
(1, 'MONDAY', '14:00:00', '18:00:00'),
(2, 'TUESDAY', '10:00:00', '15:00:00'),
(3, 'WEDNESDAY', '16:00:00', '20:00:00'),
(4, 'THURSDAY', '09:00:00', '13:00:00');

-- Insert dummy Person Ratings
INSERT INTO `Person_Rating` (rating_id, class_id, tutee_id, tutor_id, personRating, userFeedback) VALUES
(1, 1, 3, 1, 4, 'Great explanations of complex machine learning concepts'),
(2, 3, 2, 2, 5, 'Exceptional tutoring, very patient and clear'),
(3, 4, 1, 1, 3, 'Helpful but could provide more detailed guidance');

-- Insert dummy Event Ratings
INSERT INTO `Event_Rating` (rating_id, event_id, rating, eventFeedback) VALUES
(1, 1, 5, 'Insightful workshop with practical AI ethics discussions'),
(2, 2, 4, 'Good networking opportunities, could use more structured interactions'),
(3, 3, 5, 'Exceptional conference with cutting-edge research presentations');

SET FOREIGN_KEY_CHECKS = 1;