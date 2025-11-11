# IIT Jammu Indoor Sports Booking and Management System

A web-based booking and resource management system designed for the IIT Jammu Indoor Sports Complex.  
The system enables students and faculty to book sports facilities, manage participants, reserve equipment, and avoid scheduling conflicts through an intuitive PHP–MySQL application.

---

## 1. Overview

This project was developed as part of a DBMS application mini-project.  
It demonstrates:

- Relational database design (InnoDB with foreign key constraints)  
- Backend development in PHP  
- Server-side validation for overlapping bookings  
- A clean, functional user interface for interacting with the database  

---

## 2. Features

### **User Management**
- Student and Faculty user roles  
- Unique email identification  
- Basic contact details  

### **Facility & Unit Management**
- List of sports facilities (e.g., Badminton, Table Tennis)  
- Each facility can have multiple units (courts, tables, etc.)  

### **Booking System**
- Book specific units for a date and time  
- Overlap-free booking validation  
- Edit or cancel existing bookings  
- View bookings by user or facility  

### **Participants & Equipment**
- Add multiple participants to a booking  
- Allocate equipment during booking  
- Maintain quantity tracking per unit  

---

## 3. System Architecture

**Frontend:** PHP, HTML, CSS  
**Backend:** PHP (XAMPP Apache Server)  
**Database:** MySQL with InnoDB engine  
**Hosting Environment:** Local (XAMPP)

---

## 4. Database Design (Summary)

The database consists of six core tables:

| Table | Purpose |
|-------|---------|
| **Users** | Stores user details and roles |
| **Facilities** | Lists sports facilities |
| **FacilityUnits** | Stores units under each facility |
| **Bookings** | Core booking table with date/time |
| **BookingParticipants** | Users participating in a booking |
| **BookedEquipments** | Equipment reserved under a booking |

Full SQL schema is included in the repository as:


---

## 5. Screenshots

Home Page  
(Place your screenshot here)

```markdown
![Home Page](./Screenshots/home_page.png)
