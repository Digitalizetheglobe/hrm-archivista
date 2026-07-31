# Detailed Explanation: Salary Slip & Calendar Analysis (June 2026)

This document provides a comprehensive, step-by-step breakdown of the salary slip generated for **Ashwini Hunagund (ARC054)** for the month of **June 2026**. It explains the mathematical calculations, identifies discrepancies between the attendance calendar and the payslip, and details the programmatic reasons behind these calculations.

---

## 1. Executive Summary

A review of the payslip and calendar reveals three major discrepancies caused by bugs in the payroll calculation code (`pdf.blade.php`):
1. **Weekly Offs inflated to 8.00 days**: The code counts all Saturdays and Sundays in the month as week-offs, ignoring the company policy of only 4 Sundays and 2 Saturdays (2nd & 4th).
2. **Days Payable inflated to 34.00 days**: June only has 30 days, but the payslip shows 34.00 days payable. This is caused by double-counting working Saturdays and inflating a half-day leave to a full day.
3. **Negative Absent Deduction (₹-4,666.67)**: Because the days payable (34) exceeded the calendar days of the month (30), the calculated absent days became negative (-4). This negative deduction was subtracted from gross earnings, **increasing the net pay from ₹35,000.00 to ₹37,666.67**.

---

## 2. Why does the Payslip show 8.00 Weekly Offs?

### The Company Policy vs. The Code Bug
* **The Policy (Calendar)**: All **4 Sundays** + the **2nd and 4th Saturdays** are designated weekly offs.
* **The Bug in `pdf.blade.php`**: The code hardcodes that **every Saturday and Sunday** is a weekly off:
  ```php
  foreach ($period as $day) {
      // Count Saturdays (6) and Sundays (7)
      if ($day->format('N') == 6 || $day->format('N') == 7) {
          $weeklyOff++;
      }
  }
  ```

### The Math for June 2026
In the month of June 2026, there are exactly 4 Saturdays and 4 Sundays:
* **Saturdays**: June 6, June 13, June 20, June 27 (4 days)
* **Sundays**: June 7, June 14, June 21, June 28 (4 days)

Because the system counts all of them, it yields **8.00 days** of Weekly Off on the payslip.
However, according to your policy, only 6 days should be week-offs:
* **Sundays (4 days)**: June 7, 14, 21, 28 (All weekly offs)
* **Saturdays (2 days)**: June 13 (2nd Sat) and June 27 (4th Sat) (Weekly offs)
* *June 6 (1st Sat) and June 20 (3rd Sat) should be regular working days.*

---

## 3. Why are there 34.00 Days Payable in June (a 30-Day Month)?

The system calculates **Days Payable** using this formula:
$$\text{Days Payable} = \text{Present Days} + \text{Weekly Off} + \text{Total Leave} + \text{Public Holidays (PH)} - \text{LWP}$$

Let's plug in the numbers computed by the system:

| Component | Days | Explanation |
| :--- | :--- | :--- |
| **Present Days** | **24.00** | The count of attendance records in the database for June. (Currently 23 records exist, but 24 were counted when the slip was generated, likely due to a temporary weekend punch or a duplicate punch). |
| **Weekly Off** | **8.00** | All 4 Saturdays and 4 Sundays in June (due to the bug described in Section 2). |
| **Total Leave** | **2.00** | 1.0 day for Casual Leave (June 12) + 1.0 day for the Half-Day Leave (June 6). |
| **PH (Public Holiday)**| **0.00** | No public holidays were registered for June. |
| **LWP (Leave Without Pay)**| **0.00** | No unpaid leaves were taken. |
| **Total Days Payable** | **34.00** | $24.00 \text{ (Present)} + 8.00 \text{ (Week Off)} + 2.00 \text{ (Leave)} = 34.00$ |

### The Double-Counting Breakdown
This impossible total of 34 days is caused by two compounding errors:
1. **Double Counting Working Saturdays (+2.00 Days)**:
   The employee worked on **June 6** (times: 09:26 - 13:30) and **June 20** (times: 09:33 - 18:06). Since they clocked in, these days were counted in **Present Days**. However, because they are Saturdays, they were also counted in **Weekly Off**. This added 2 extra days to the total.
2. **Half-Day Leave Stored as a Full Day (+1.00 Day)**:
   On **June 6**, the employee worked a half-day and applied for a half-day leave. The database stores this leave with `total_leave_days = 1` instead of `0.5`. This caused June 6 to count as:
   * **1.0 day Present** (since they clocked in)
   * **1.0 day Leave** (due to the DB record)
   * **1.0 day Weekly Off** (since the code counts all Saturdays)
   
   This means **June 6 alone generated 3.0 days payable** on the payslip!

---

## 4. How does the Absent Deduction and Net Pay Work?

### The Formulas Used
The system calculates the **Absent Days** and **Absent Deduction** using these formulas:
$$\text{Absent Days} = \text{Total Days in Month} - \text{Calculated Days Payable}$$
$$\text{Per Day Salary} = \frac{\text{Gross Salary}}{30}$$
$$\text{Absent Deduction} = \text{Absent Days} \times \text{Per Day Salary}$$

### The Math for Ashwini
1. **Absent Days**:
   $$\text{Absent Days} = 30 \text{ (June Days)} - 34.00 \text{ (Days Payable)} = -4.00 \text{ days}$$
   *Since Days Payable (34) exceeded the actual month length (30), the employee got a negative absent count of **-4 days**.*
2. **Per Day Salary**:
   $$\text{Per Day Salary} = \frac{₹35,000.00}{30} = \text{₹1,166.6667 per day}$$
3. **Absent Deduction**:
   $$\text{Absent Deduction} = -4.00 \times \text{₹1,166.6667} = \text{₹-4,666.67}$$

### Calculating Total Deductions and Net Pay
Normally, deductions subtract money from the salary. However, because the Absent Deduction is negative, it actually **increases** the employee's pay.

$$\text{Total Deductions (B)} = \text{PF} + \text{PT} + \text{Absent Deduction}$$
$$\text{Total Deductions (B)} = ₹1,800.00 + ₹200.00 + (₹-4,666.67) = \text{₹-2,666.67}$$

$$\text{Net Pay} = \text{Gross Earning (A)} - \text{Total Deductions (B)}$$
$$\text{Net Pay} = ₹35,000.00 - (₹-2,666.67) = \text{₹37,666.67}$$

> [!IMPORTANT]
> **Summary of the Financial Impact**:
> Because the system incorrectly computed 34 days payable and a negative absent deduction, the employee was **overpaid by ₹2,666.67**! Instead of taking home less than their ₹35,000.00 gross salary after PF (₹1,800) and PT (₹200) deductions, they received a net payout of **₹37,666.67**.

---

## 5. How to Fix this in the Code

To prevent this from happening in future payslips, the following changes should be made to the calculations in `pdf.blade.php`:

1. **Proper Weekly Off Calculation**: Modify the loop to match company policy (Saturdays should only be counted if they are the 2nd or 4th Saturday of the month).
2. **Weekend Work Overlap Check**: If an employee clocks in on a weekly off day, do not double-count that day in both `Present Days` and `Weekly Off`.
3. **Proper Half-Day Leave Tracking**: Read the `leave_duration` field. If it is `half_day`, add `0.5` to the leave days instead of `1.0`.
