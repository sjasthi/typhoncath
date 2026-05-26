📄 CRM System for Typhon Cat
Software Requirements Specification (SRS – Capstone Level)
1. Introduction
1.1 Purpose

This document defines the requirements for a lightweight Customer Relationship Management (CRM) system developed for Typhon Cat. The system is designed to support core business operations including customer management, sales pipeline tracking, marketing campaigns, and inventory management.

The system will be implemented as a monolithic web application using PHP and MySQL.

1.2 Project Goals

The goal of this CRM system is to:

Centralize customer and business interaction data
Track sales opportunities through RFQ lifecycle
Manage digital marketing campaigns
Maintain inventory visibility for products/services
Provide a unified operational dashboard for decision-making
1.3 Scope

The system will include four primary modules:

S1 — Customer Management
Customer profile creation and maintenance
Contact management
Interaction history tracking
S2 — RFQ / Pipeline Management
RFQ creation and tracking
Sales pipeline stages
Quote and deal tracking
S3 — Digital Campaign Management
Campaign creation and tracking
Customer segmentation
Campaign performance metrics
S4 — Inventory Management
Product catalog
Stock tracking
Inventory allocation to RFQs
Integration Layer (S5)
Cross-module data sharing
Reporting and unified dashboard
Internal APIs for module communication
1.4 Technology Stack
Frontend: HTML5, CSS3, JavaScript, jQuery, Bootstrap
Backend: PHP (Apache or Nginx server)
Database: MySQL
Hosting: Local development + optional cloud deployment
Version Control: Git / GitHub
2. Overall System Description
2.1 System Perspective

The CRM is a web-based monolithic application where all modules operate within a shared codebase and database.

2.2 User Roles
2.2.1 Admin
Full system access
User management
System configuration
2.2.2 Sales User
Manage customers (S1)
Create and track RFQs (S2)
2.2.3 Marketing User
Manage campaigns (S3)
View campaign analytics
2.2.4 Inventory Manager
Manage products and stock (S4)
Update inventory availability
2.3 Assumptions
Users have basic browser access
Internet connectivity available
Single organization deployment (Typhon Cat internal system)
3. Functional Requirements
S1 — Customer Management
FR-S1.1 Customer Creation

System shall allow users to create customer records including:

Name / Company
Email / Phone
Address
Industry / Tags
FR-S1.2 Customer Search

System shall allow searching customers by:

Name
Email
Tags
FR-S1.3 Interaction Tracking

System shall log:

Calls
Emails
Notes
Meeting history
S2 — RFQ / Pipeline Management
FR-S2.1 RFQ Creation

System shall allow creation of RFQs linked to a customer.

FR-S2.2 Pipeline Stages

RFQs shall move through:

New
In Review
Quoted
Negotiation
Won / Lost
FR-S2.3 Quote Tracking

System shall store:

Quote amount
Discount
Validity period
FR-S2.4 Deal Conversion

System shall allow conversion of RFQ into a “Deal Won”.

S3 — Digital Campaign Management
FR-S3.1 Campaign Creation

System shall allow creation of campaigns:

Email campaigns
SMS campaigns (optional simulation)
FR-S3.2 Audience Selection

Campaigns shall be linked to:

Customer segments
Tags or filters
FR-S3.3 Campaign Tracking

System shall track:

Sent count
Open rate (simulated or logged manually)
Click rate (optional)
S4 — Inventory Management
FR-S4.1 Product Management

System shall allow creation of products:

Product name
SKU
Price
Description
FR-S4.2 Stock Management

System shall track:

Available quantity
Reserved quantity
FR-S4.3 RFQ Reservation

System shall allow inventory to be reserved for RFQs.

S5 — Integration Layer
FR-S5.1 Unified Dashboard

System shall provide a dashboard showing:

Customer count
Active RFQs
Campaign performance
Inventory status
FR-S5.2 Cross-module linking

System shall ensure:

RFQ → Customer linkage
Campaign → Customer linkage
RFQ → Inventory linkage
4. Non-Functional Requirements
NFR1 Performance
Page load time < 3 seconds
Queries optimized for MySQL indexing
NFR2 Security
Password hashing (bcrypt)
Role-based access control
Session management
NFR3 Usability
Bootstrap-based responsive UI
Simple navigation menus
NFR4 Maintainability
Modular PHP folder structure
Clean separation of concerns
NFR5 Reliability
Database backup support
Error logging enabled
5. System Architecture
5.1 Architecture Style
Monolithic 3-tier architecture:
Presentation Layer (UI)
Application Layer (PHP logic)
Data Layer (MySQL)
5.2 Module Structure (Recommended)
/crm-typhoncat
  /modules
    /customer
    /rfq
    /campaign
    /inventory
    /integration
  /public
    /css
    /js
    /bootstrap
  /config
  /includes
  /database
6. Database Design (High-Level)
Core Tables
Customers
customer_id (PK)
name
email
phone
industry
created_at
RFQs
rfq_id (PK)
customer_id (FK)
status
estimated_value
created_at
Campaigns
campaign_id (PK)
name
type
target_segment
Inventory
product_id (PK)
sku
stock_qty
reserved_qty
Interaction Logs
log_id
customer_id
type
notes
timestamp
7. User Interface Requirements
UI-1 Dashboard
Summary widgets (customers, RFQs, campaigns, inventory)
UI-2 Customer Screen
List + detail view
Add/Edit customer
UI-3 RFQ Screen
Pipeline board or table view
UI-4 Campaign Screen
Campaign creation + results
UI-5 Inventory Screen
Product list + stock updates
8. Capstone Team Structure (4 Students)
Student 1
S1 Customer Management
UI for customer module
Student 2
S2 RFQ / Pipeline
Deal tracking logic
Student 3
S3 Campaign module
Basic analytics
Student 4
S4 Inventory + S5 integration dashboard
9. Deliverables
Working web application
MySQL database schema
Source code (GitHub)
Documentation (this SRS + user guide)
Final presentation demo
Test cases
10. Future Enhancements (Out of Scope)
AI-based lead scoring
Mobile app
Advanced workflow automation
Email server integration
Payment integration
11. Success Criteria

The project will be considered successful if:

All 4 modules are functional
Data flows correctly between modules

Users can complete full cycle:

Customer → RFQ → Campaign → Inventory allocation

System is usable via browser
Basic security and role access implemented
