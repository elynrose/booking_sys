# Error Codes Documentation

This document provides comprehensive information about all error codes used in the Gym Management System.

## Overview

Error codes are categorized by type and provide detailed information for staff to diagnose and resolve issues.

## Authentication Errors

| Error Code | Description | Common Causes | Resolution |
|------------|-------------|---------------|------------|
| `AUTH_001` | Member ID not found | Invalid member ID format, member not in database | Verify member ID, create account if needed |
| `AUTH_002` | Member ID verification failed | Session expired, member ID mismatch | Re-verify member ID, clear session |
| `AUTH_003` | Session expired | User not logged in, session timeout | Re-authenticate user |
| `AUTH_004` | Invalid credentials | Unknown cause | Contact system administrator |
| `AUTH_005` | Account locked | Unknown cause | Contact system administrator |
| `AUTH_006` | Email not verified | Unknown cause | Contact system administrator |
| `AUTH_007` | Two-factor authentication required | Unknown cause | Contact system administrator |
| `AUTH_008` | Rate limit exceeded | Unknown cause | Contact system administrator |
| `AUTHZ_001` | Insufficient permissions | User lacks required permissions | Assign required permissions to user |
| `AUTHZ_002` | Resource ownership mismatch | User trying to access another user's resource | Verify resource ownership |
| `AUTHZ_003` | Role-based access denied | User role not authorized for action | Assign appropriate role to user |
| `AUTHZ_004` | Admin access required | Unknown cause | Contact system administrator |
| `AUTHZ_005` | Trainer access required | Unknown cause | Contact system administrator |

## Authorization Errors

| Error Code | Description | Common Causes | Resolution |
|------------|-------------|---------------|------------|
| `AUTHZ_001` | Insufficient permissions | User lacks required permissions | Assign required permissions to user |
| `AUTHZ_002` | Resource ownership mismatch | User trying to access another user's resource | Verify resource ownership |
| `AUTHZ_003` | Role-based access denied | User role not authorized for action | Assign appropriate role to user |
| `AUTHZ_004` | Admin access required | Unknown cause | Contact system administrator |
| `AUTHZ_005` | Trainer access required | Unknown cause | Contact system administrator |

## Validation Errors

| Error Code | Description | Common Causes | Resolution |
|------------|-------------|---------------|------------|
| `VALID_001` | Required field missing | Required field not provided | Provide required field data |
| `VALID_002` | Invalid data format | Invalid data format provided | Correct data format |
| `VALID_003` | Data out of range | Value outside acceptable range | Use value within acceptable range |
| `VALID_004` | Duplicate entry | Duplicate entry or non-existent reference | Use unique value or valid reference |
| `VALID_005` | Invalid date/time | Invalid date/time format | Use correct date/time format |
| `VALID_006` | File upload failed | Unknown cause | Contact system administrator |
| `VALID_007` | Invalid file type | Unknown cause | Contact system administrator |
| `VALID_008` | File size exceeded | Unknown cause | Contact system administrator |

## Business Logic Errors

| Error Code | Description | Common Causes | Resolution |
|------------|-------------|---------------|------------|
| `BUSINESS_001` | Schedule not available | Schedule inactive or disabled | Activate schedule in admin panel |
| `BUSINESS_002` | Booking already exists | User already has active booking | Cancel existing booking first |
| `BUSINESS_003` | No sessions remaining | No sessions remaining for booking | Purchase additional sessions |
| `BUSINESS_004` | Trainer not available | Trainer unavailable at requested time | Select different time or trainer |
| `BUSINESS_005` | Class is full | Class at maximum capacity | Wait for spot or join waitlist |
| `BUSINESS_006` | Class has ended | Class end date has passed | Select future class |
| `BUSINESS_007` | Check-in already completed | User already checked in today | Check out first, then check in |
| `BUSINESS_008` | Check-out already completed | User already checked out today | Already completed for today |
| `BUSINESS_009` | Payment required | Payment required before action | Complete payment process |
| `BUSINESS_010` | Invalid booking status | Booking in invalid state for action | Update booking status |
| `BUSINESS_011` | Future check-in not allowed | Attempting to check in for future class | Wait until class starts |
| `BUSINESS_012` | Late check-in limit exceeded | Late check-in limit exceeded | Contact staff for override |
| `BUSINESS_013` | Child not found | No check-in record found for today | Check in first |
| `BUSINESS_014` | Category not found | Unknown cause | Contact system administrator |
| `BUSINESS_015` | Price calculation error | Unknown cause | Contact system administrator |

## System Errors

| Error Code | Description | Common Causes | Resolution |
|------------|-------------|---------------|------------|
| `SYSTEM_001` | Database connection failed | Database connection issues | Contact system administrator |
| `SYSTEM_002` | File system error | File system access problems | Check file permissions |
| `SYSTEM_003` | Cache error | Cache service unavailable | Restart cache service |
| `SYSTEM_004` | Queue processing failed | Queue processing failed | Check queue workers |
| `SYSTEM_005` | Email sending failed | Email service unavailable | Check email configuration |
| `SYSTEM_006` | SMS sending failed | SMS service unavailable | Check SMS service status |
| `SYSTEM_007` | Payment gateway error | Payment gateway error | Check payment gateway |
| `SYSTEM_008` | Storage service error | Storage service error | Check storage service |

## External Service Errors

| Error Code | Description | Common Causes | Resolution |
|------------|-------------|---------------|------------|
| `EXTERNAL_001` | Payment gateway timeout | Payment gateway timeout | Wait for service recovery |
| `EXTERNAL_002` | SMS service unavailable | SMS service down | Use alternative notification |
| `EXTERNAL_003` | Email service down | Email service down | Use alternative notification |
| `EXTERNAL_004` | Third-party API error | Third-party API error | Contact service provider |
| `EXTERNAL_005` | Cloud storage error | Cloud storage error | Check cloud storage status |

## Troubleshooting Guide

### General Troubleshooting Steps

1. **Check Error Code**: Always note the specific error code for reference
2. **Review Context**: Check the context information provided with the error
3. **Verify User Permissions**: Ensure user has appropriate roles and permissions
4. **Check System Status**: Verify all services are running properly
5. **Review Logs**: Check application logs for additional details

### Common Issues and Solutions

#### Authentication Issues
- **Member ID not found**: Verify member exists in database
- **Session expired**: Ask user to re-verify member ID
- **Permission denied**: Check user roles and permissions

#### Booking Issues
- **Class full**: Check maximum participants limit
- **Trainer unavailable**: Check trainer availability calendar
- **Duplicate booking**: Cancel existing booking first

#### Check-in Issues
- **Future check-in**: Wait until class starts
- **Already checked in**: Complete checkout first
- **No sessions remaining**: Purchase additional sessions

## Resolution Guide

### Immediate Actions

1. **Acknowledge the Error**: Inform user that you understand the issue
2. **Gather Information**: Collect error code and context details
3. **Check System Status**: Verify all services are operational
4. **Apply Resolution**: Follow the specific resolution steps
5. **Test Solution**: Verify the issue is resolved

### Escalation Procedures

#### Level 1 - Staff Resolution
- Authentication issues
- Booking conflicts
- Basic permission issues

#### Level 2 - Technical Support
- System errors
- Database issues
- External service failures

#### Level 3 - System Administrator
- Critical system failures
- Security issues
- Infrastructure problems

## Staff Guidelines

### When Handling Errors

1. **Always Log**: Record error codes and context for tracking
2. **Be Professional**: Maintain calm and helpful demeanor
3. **Explain Clearly**: Use simple language to explain issues
4. **Provide Alternatives**: Suggest alternative solutions when possible
5. **Follow Up**: Ensure issues are fully resolved

### Communication Guidelines

#### For Users
- Explain the issue in simple terms
- Provide clear next steps
- Offer alternative solutions
- Maintain positive attitude

#### For Technical Staff
- Include error code and reference ID
- Provide full context information
- Include user actions that led to error
- Specify urgency level

### Prevention Tips

1. **Regular Training**: Keep staff updated on system changes
2. **Documentation**: Maintain up-to-date procedures
3. **Monitoring**: Watch for recurring error patterns
4. **Feedback**: Collect user feedback on error messages
5. **Improvement**: Continuously improve error handling

