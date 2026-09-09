-- ============================================================
-- Smart DUET Admission Management System
-- Database: smart_duet
-- DBMS: MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS smart_duet
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE smart_duet;


-- ============================================================
-- 1. USERS
-- Login / Signup information
-- ============================================================

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    email VARCHAR(100) NOT NULL UNIQUE,

    mobile VARCHAR(15) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    role ENUM('applicant', 'admin') NOT NULL DEFAULT 'applicant',

    status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- ============================================================
-- 2. APPLICANTS
-- Personal information of applicant
-- ============================================================

CREATE TABLE applicants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL UNIQUE,

    full_name VARCHAR(150) NOT NULL,

    father_name VARCHAR(150) NOT NULL,

    mother_name VARCHAR(150) NOT NULL,

    date_of_birth DATE NOT NULL,

    gender ENUM('Male', 'Female', 'Other') NOT NULL,

    identity_number VARCHAR(50) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_applicant_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- ============================================================
-- 3. DEPARTMENTS
-- DUET Undergraduate Admission Departments
-- ============================================================

CREATE TABLE departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    code VARCHAR(10) NOT NULL UNIQUE,

    name VARCHAR(150) NOT NULL,

    faculty VARCHAR(150) NOT NULL,

    degree VARCHAR(100) NOT NULL DEFAULT 'B.Sc. Engineering',

    duration VARCHAR(50) NOT NULL DEFAULT '4 Years',

    seats INT UNSIGNED NOT NULL DEFAULT 0,

    is_active TINYINT(1) NOT NULL DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ============================================================
-- Insert 2026 Undergraduate Admission Departments
-- ============================================================

INSERT INTO departments
(code, name, faculty, degree, duration, seats)
VALUES

(
    'CE',
    'Civil Engineering',
    'Faculty of Civil Engineering',
    'B.Sc. Engineering',
    '4 Years',
    120
),

(
    'EEE',
    'Electrical and Electronic Engineering',
    'Faculty of Electrical and Electronic Engineering',
    'B.Sc. Engineering',
    '4 Years',
    120
),

(
    'CSE',
    'Computer Science and Engineering',
    'Faculty of Electrical and Electronic Engineering',
    'B.Sc. Engineering',
    '4 Years',
    120
),

(
    'ME',
    'Mechanical Engineering',
    'Faculty of Mechanical Engineering',
    'B.Sc. Engineering',
    '4 Years',
    120
),

(
    'TE',
    'Textile Engineering',
    'Faculty of Mechanical Engineering',
    'B.Sc. Engineering',
    '4 Years',
    120
),

(
    'Arch',
    'Architecture',
    'Faculty of Civil Engineering',
    'Bachelor of Architecture',
    '5 Years',
    30
),

(
    'IPE',
    'Industrial and Production Engineering',
    'Faculty of Mechanical Engineering',
    'B.Sc. Engineering',
    '4 Years',
    30
),

(
    'MME',
    'Materials and Metallurgical Engineering',
    'Faculty of Mechanical Engineering',
    'B.Sc. Engineering',
    '4 Years',
    30
),

(
    'CHE',
    'Chemical Engineering',
    'Faculty of Mechanical Engineering',
    'B.Sc. Engineering',
    '4 Years',
    30
),

(
    'FE',
    'Food Engineering',
    'Faculty of Mechanical Engineering',
    'B.Sc. Engineering',
    '4 Years',
    30
);


-- ============================================================
-- ============================================================
-- 4. DIPLOMA TECHNOLOGIES
-- Corrected technology list for DUET undergraduate admission
-- ============================================================

CREATE TABLE diploma_technologies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL UNIQUE,

    is_active TINYINT(1) NOT NULL DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO diploma_technologies (name)
VALUES

('Civil Technology'),
('Surveying Technology'),
('Environmental Technology'),

('Electrical Technology'),
('Electromedical Technology'),
('Electronics Technology'),
('Instrumentation and Process Control Technology'),
('Telecommunication Technology'),

('Mechanical Technology'),
('Power Technology'),
('Refrigeration and Air Conditioning (RAC) Technology'),
('Automobile Technology'),
('Mechatronics Technology'),
('Ceramic Technology'),
('Glass Technology'),
('Ship Building Technology'),
('Marine Technology'),
('Mining and Mine Survey Technology'),

('Computer Science and Technology'),
('Computer Technology'),
('Data Telecommunication and Networking Technology'),
('Graphics Design Technology'),
('Printing Technology'),

('Textile Technology'),
('Jute Technology'),
('Garments and Pattern Making Technology'),

('Architecture Technology'),
('Architecture and Interior Design Technology'),

('Chemical Technology'),
('Food Technology'),
('Agriculture Technology');


-- ============================================================
-- 5. TECHNOLOGY → DEPARTMENT ELIGIBILITY
-- Corrected DUET department/technology mapping
-- ============================================================

CREATE TABLE technology_department_eligibility (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    technology_id INT UNSIGNED NOT NULL,

    department_id INT UNSIGNED NOT NULL,

    UNIQUE KEY unique_technology_department
    (
        technology_id,
        department_id
    ),

    CONSTRAINT fk_tde_technology
        FOREIGN KEY (technology_id)
        REFERENCES diploma_technologies(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_tde_department
        FOREIGN KEY (department_id)
        REFERENCES departments(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- ============================================================
-- CIVIL ENGINEERING (CE)
-- Civil / Surveying / Environmental
-- ============================================================

INSERT INTO technology_department_eligibility (technology_id, department_id)
SELECT t.id, d.id
FROM diploma_technologies t
JOIN departments d
WHERE t.name IN (
    'Civil Technology',
    'Surveying Technology',
    'Environmental Technology'
)
AND d.code = 'CE';


-- ============================================================
-- ELECTRICAL AND ELECTRONIC ENGINEERING (EEE)
-- ============================================================

INSERT INTO technology_department_eligibility (technology_id, department_id)
SELECT t.id, d.id
FROM diploma_technologies t
JOIN departments d
WHERE t.name IN (
    'Electrical Technology',
    'Electromedical Technology',
    'Electronics Technology',
    'Instrumentation and Process Control Technology',
    'Telecommunication Technology'
)
AND d.code = 'EEE';


-- ============================================================
-- MECHANICAL ENGINEERING (ME)
-- ============================================================

INSERT INTO technology_department_eligibility (technology_id, department_id)
SELECT t.id, d.id
FROM diploma_technologies t
JOIN departments d
WHERE t.name IN (
    'Mechanical Technology',
    'Power Technology',
    'Refrigeration and Air Conditioning (RAC) Technology',
    'Instrumentation and Process Control Technology',
    'Automobile Technology',
    'Mechatronics Technology',
    'Ceramic Technology',
    'Glass Technology',
    'Ship Building Technology',
    'Marine Technology',
    'Mining and Mine Survey Technology'
)
AND d.code = 'ME';


-- ============================================================
-- COMPUTER SCIENCE AND ENGINEERING (CSE)
-- ============================================================

INSERT INTO technology_department_eligibility (technology_id, department_id)
SELECT t.id, d.id
FROM diploma_technologies t
JOIN departments d
WHERE t.name IN (
    'Computer Science and Technology',
    'Computer Technology',
    'Data Telecommunication and Networking Technology',
    'Electronics Technology',
    'Graphics Design Technology',
    'Printing Technology'
)
AND d.code = 'CSE';


-- ============================================================
-- TEXTILE ENGINEERING (TE)
-- ============================================================

INSERT INTO technology_department_eligibility (technology_id, department_id)
SELECT t.id, d.id
FROM diploma_technologies t
JOIN departments d
WHERE t.name IN (
    'Textile Technology',
    'Jute Technology',
    'Garments and Pattern Making Technology'
)
AND d.code = 'TE';


-- ============================================================
-- ARCHITECTURE (Arch)
-- ============================================================

INSERT INTO technology_department_eligibility (technology_id, department_id)
SELECT t.id, d.id
FROM diploma_technologies t
JOIN departments d
WHERE t.name IN (
    'Architecture Technology',
    'Architecture and Interior Design Technology'
)
AND d.code = 'Arch';


-- ============================================================
-- INDUSTRIAL AND PRODUCTION ENGINEERING (IPE)
-- ============================================================

INSERT INTO technology_department_eligibility (technology_id, department_id)
SELECT t.id, d.id
FROM diploma_technologies t
JOIN departments d
WHERE t.name IN (
    'Mechanical Technology',
    'Power Technology',
    'Chemical Technology',
    'Automobile Technology',
    'Refrigeration and Air Conditioning (RAC) Technology',
    'Food Technology',
    'Marine Technology',
    'Mechatronics Technology',
    'Ship Building Technology',
    'Instrumentation and Process Control Technology'
)
AND d.code = 'IPE';


-- ============================================================
-- MATERIALS AND METALLURGICAL ENGINEERING (MME)
-- ============================================================

INSERT INTO technology_department_eligibility (technology_id, department_id)
SELECT t.id, d.id
FROM diploma_technologies t
JOIN departments d
WHERE t.name IN (
    'Mechanical Technology',
    'Power Technology',
    'Automobile Technology',
    'Refrigeration and Air Conditioning (RAC) Technology',
    'Chemical Technology',
    'Mining and Mine Survey Technology',
    'Ceramic Technology',
    'Glass Technology',
    'Ship Building Technology'
)
AND d.code = 'MME';


-- ============================================================
-- CHEMICAL ENGINEERING (CHE)
-- ============================================================

INSERT INTO technology_department_eligibility (technology_id, department_id)
SELECT t.id, d.id
FROM diploma_technologies t
JOIN departments d
WHERE t.name IN (
    'Chemical Technology',
    'Mechanical Technology',
    'Power Technology',
    'Refrigeration and Air Conditioning (RAC) Technology',
    'Instrumentation and Process Control Technology',
    'Automobile Technology',
    'Mechatronics Technology',
    'Ceramic Technology',
    'Glass Technology',
    'Ship Building Technology',
    'Marine Technology',
    'Mining and Mine Survey Technology',
    'Environmental Technology'
)
AND d.code = 'CHE';


-- ============================================================
-- FOOD ENGINEERING (FE)
-- ============================================================

INSERT INTO technology_department_eligibility (technology_id, department_id)
SELECT t.id, d.id
FROM diploma_technologies t
JOIN departments d
WHERE t.name IN (
    'Food Technology',
    'Refrigeration and Air Conditioning (RAC) Technology',
    'Agriculture Technology'
)
AND d.code = 'FE';


-- 6. APPLICATIONS
-- Main admission application information
-- ============================================================

CREATE TABLE applications (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    application_no VARCHAR(30) NOT NULL UNIQUE,

    applicant_id INT UNSIGNED NOT NULL,

    admission_year YEAR NOT NULL,

    ssc_gpa DECIMAL(3,2) NOT NULL,

    diploma_cgpa DECIMAL(3,2) NOT NULL,

    diploma_passing_year YEAR NOT NULL,

    technology_id INT UNSIGNED NOT NULL,

    department_id INT UNSIGNED NOT NULL,

    quota ENUM(
        'None',
        'Freedom Fighter''s Son/Daughter',
        'Tribal'
    ) NOT NULL DEFAULT 'None',

    status ENUM(
        'draft',
        'payment_pending',
        'submitted',
        'under_review',
        'accepted',
        'rejected'
    ) NOT NULL DEFAULT 'draft',

    submitted_at DATETIME NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_application_applicant
        FOREIGN KEY (applicant_id)
        REFERENCES applicants(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_application_technology
        FOREIGN KEY (technology_id)
        REFERENCES diploma_technologies(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_application_department
        FOREIGN KEY (department_id)
        REFERENCES departments(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);


-- ============================================================
-- 7. DOCUMENTS
--
-- IMPORTANT:
-- Only documents required by current apply.php are stored.
--
-- NO SSC certificate
-- NO Diploma certificate
-- ============================================================

CREATE TABLE documents (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    application_id INT UNSIGNED NOT NULL UNIQUE,

    applicant_photo VARCHAR(255) NOT NULL,

    signature VARCHAR(255) NOT NULL,

    identity_document VARCHAR(255) NOT NULL,

    quota_document VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_document_application
        FOREIGN KEY (application_id)
        REFERENCES applications(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- ============================================================
-- 8. PAYMENTS
-- Application fee payment
-- ============================================================

CREATE TABLE payments (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    application_id INT UNSIGNED NOT NULL UNIQUE,

    payment_method ENUM(
        'Rocket',
        'bKash',
        'Agrani Education Fee Pay'
    ) NOT NULL,

    amount DECIMAL(10,2) NOT NULL DEFAULT 1500.00,

    transaction_id VARCHAR(100) NOT NULL,

    payment_status ENUM(
        'pending',
        'paid',
        'failed'
    ) NOT NULL DEFAULT 'pending',

    paid_at DATETIME NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_payment_application
        FOREIGN KEY (application_id)
        REFERENCES applications(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- ============================================================
-- 9. NOTICES
-- Dynamic admission notices
-- ============================================================

CREATE TABLE notices (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(255) NOT NULL,

    description TEXT NULL,

    pdf_file VARCHAR(255) NULL,

    admission_year YEAR NULL,

    published_date DATE NOT NULL,

    status ENUM(
        'published',
        'draft'
    ) NOT NULL DEFAULT 'published',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- ============================================================
-- 10. ADMIT CARDS
-- Applicant admit card information
-- ============================================================

CREATE TABLE admit_cards (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    application_id INT UNSIGNED NOT NULL UNIQUE,

    admit_card_no VARCHAR(50) NOT NULL UNIQUE,

    exam_date DATE NULL,

    exam_shift VARCHAR(50) NULL,

    exam_center VARCHAR(255) NULL,

    seat_number VARCHAR(50) NULL,

    pdf_file VARCHAR(255) NULL,

    status ENUM(
        'not_published',
        'published'
    ) NOT NULL DEFAULT 'not_published',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_admit_card_application
        FOREIGN KEY (application_id)
        REFERENCES applications(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- ============================================================
-- 11. RESULTS
-- Admission result for application
-- ============================================================

CREATE TABLE results (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    application_id INT UNSIGNED NOT NULL UNIQUE,

    merit_position INT UNSIGNED NULL,

    result_status ENUM(
        'pending',
        'selected',
        'waiting',
        'not_selected'
    ) NOT NULL DEFAULT 'pending',

    published_date DATE NULL,

    remarks VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_result_application
        FOREIGN KEY (application_id)
        REFERENCES applications(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- ============================================================
-- INDEXES
-- ============================================================

CREATE INDEX idx_applicant_user
ON applicants(user_id);

CREATE INDEX idx_application_applicant
ON applications(applicant_id);

CREATE INDEX idx_application_year
ON applications(admission_year);

CREATE INDEX idx_application_department
ON applications(department_id);

CREATE INDEX idx_application_status
ON applications(status);

CREATE INDEX idx_notice_year
ON notices(admission_year);

CREATE INDEX idx_notice_date
ON notices(published_date);


-- ============================================================
-- END OF DATABASE
-- ============================================================