# DESIGN.md — HRIS MP (Menjadi Pengaruh Group)

> HR Information System — Web Admin Dashboard & Employee Mobile App

## ⚠️ CRITICAL: This is a LIGHT THEME application. Do NOT use dark mode. All page backgrounds are light gray (#F8FAFC). All cards and content areas are WHITE (#FFFFFF). The sidebar is WHITE with a light gray right border. The dark navy color (#130F26) is ONLY used for: active sidebar menu items, primary action buttons, and the avatar circle. Everything else should be light, clean, and white.

---

## Brand Identity

- **Product Name**: HRIS MP
- **Company**: Menjadi Pengaruh Group
- **Design Mood**: Clean, bright, minimal, professional, corporate. Light and airy with plenty of white space.
- **Overall Feel**: Like a premium SaaS admin dashboard. Think Linear, Notion, or Vercel Dashboard — clean white interfaces with subtle shadows.

---

## Typography

- **Font**: Inter (Google Fonts), weights 300–700
- **Page Title**: 24px, Bold, color #1e293b (dark slate)
- **Section Title**: 18px, Bold, color #1e293b
- **Card Label**: 14px, Semibold, UPPERCASE, color #64748b (gray)
- **Body Text**: 14px, Regular, color #1e293b
- **Secondary Text**: 14px, Regular, color #64748b (medium gray)
- **Caption/Small**: 12px, color #94a3b8 (light gray)
- **Badge Text**: 10px, Bold

---

## Color Palette

### IMPORTANT: Light Theme Colors

**Page background**: #F8FAFC (very light gray, almost white)
**Card/Content background**: #FFFFFF (pure white)
**Sidebar background**: #FFFFFF (pure white) with right border #e2e8f0

### Accent Color (used sparingly!)
- **Primary accent**: #130F26 (very dark navy) — ONLY for active menu items, primary buttons, and avatar
- **Primary gradient**: from #130F26 to #2A244A — ONLY for primary action buttons
- **Primary hover**: #1E1B3A

### Text Colors
- **Heading text**: #1e293b (dark slate) on white background
- **Body text**: #334155 (medium dark) on white background
- **Secondary text**: #64748b (medium gray) on white background
- **Placeholder text**: #94a3b8 (light gray)
- **Disabled text**: #94a3b8

### Border & Divider Colors
- **Card borders**: #f1f5f9 (very subtle, barely visible)
- **Input borders**: #e2e8f0 (light gray)
- **Table dividers**: #f1f5f9 (very light)
- **Sidebar section dividers**: #e2e8f0

### Semantic Status Colors (for badges and indicators only)
- **Success/Approved**: bg #dcfce7, text #16a34a (green)
- **Pending/Warning**: bg #fff7ed, text #f97316 (orange)
- **Rejected/Error**: bg #fef2f2, text #dc2626 (red)
- **Info**: bg #eff6ff, text #2563eb (blue)

### Action Button Colors
- **Edit button**: bg #fffbeb (light amber), text #d97706 (amber)
- **Delete button**: bg #fef2f2 (light red), text #dc2626 (red)
- **Primary button**: bg gradient #130F26→#2A244A, text white

### Stat Card Icon Backgrounds (pastel, light colors)
- Indigo: bg #eef2ff, icon #4f46e5
- Purple: bg #faf5ff, icon #9333ea
- Blue: bg #eff6ff, icon #2563eb
- Emerald: bg #ecfdf5, icon #059669
- Orange: bg #fff7ed, icon #ea580c
- Red: bg #fef2f2, icon #dc2626

---

## Shadows (subtle, not heavy)

- **Card default**: 0 2px 12px -2px rgba(0,0,0,0.04) — very subtle
- **Card hover**: 0 4px 20px -2px rgba(0,0,0,0.05) — slightly more visible
- **Button hover glow**: 0 0 15px rgba(19,15,38,0.15) — subtle navy glow

---

## Layout Structure

### Overall Page Layout
- Left: Fixed sidebar (288px wide, WHITE background, light gray right border)
- Right: Scrollable content area (light gray #F8FAFC background)
- Top of content: Sticky header bar (white with frosted glass effect)

### Sidebar (LEFT SIDE)
- Background: WHITE (#FFFFFF)
- Width: 288px
- Right border: 1px solid #e2e8f0 (light gray line)
- Top: Company logo centered (height ~112px area)
- Menu items:
  - **Active item**: bg #130F26 (dark navy), text white, rounded 12px, subtle shadow
  - **Inactive item**: text #64748b (gray), hover: bg #f8fafc, text #0f172a
  - Icon size: 20x20px, on the left of text
  - Text: 14px semibold
- Section labels: 10px, bold, uppercase, gray (#94a3b8), with horizontal lines on both sides
- Sections: "Manajemen", "Absensi", "Data Master" (collapsible with chevron)
- Bottom: "Pengaturan Akun" link + red "Logout" button

### Top Header Bar
- Background: white with 70% opacity + backdrop blur (frosted glass)
- Sticky at top
- Bottom border: 1px solid rgba(226,232,240,0.6)
- Very subtle shadow
- Left: Page title (24px, bold, #1e293b)
- Right: Notification bell icon → User name + email text → Circular avatar
- Avatar: 44px circle, gradient #130F26→#2B2545, white letter inside, white ring around it

---

## Components

### Cards
- Background: WHITE
- Border: 1px solid #f1f5f9 (barely visible)
- Border radius: 16px to 24px
- Shadow: very subtle (see shadows section)
- Padding: 24px inside
- On hover: slight lift (-1px up) with slightly stronger shadow

### Stat Cards (Dashboard)
- Background: WHITE
- Large number: 30-36px, extra bold, color #1e293b
- Label: 14px, semibold, uppercase, color #64748b
- Icon: in a pastel-colored rounded square (16px radius, 12px padding)
- Subtle decorative gradient orb in background at very low opacity (3%)

### Data Tables
- Container: WHITE background, 16px border radius, ring-1 #f1f5f9
- Header row: bg #f8fafc (almost white), text #64748b, 11px, bold, uppercase
- Body rows: WHITE background, divided by 1px #f1f5f9 lines
- Row hover: slight lift with shadow
- Cell padding: 16px vertical

### Input Fields
- Background: #f8fafc (very light gray), turns WHITE on focus
- Border: 1px solid #e2e8f0
- Border radius: 12px
- Height: 44px
- Focus state: 4px ring in rgba(19,15,38,0.1), border turns #130F26
- Label above: 14px, semibold, #334155

### Buttons
- **Primary**: gradient #130F26→#2A244A, text white, 12px radius, hover lifts up
- **Secondary**: WHITE bg, border #e2e8f0, text #334155
- **Danger**: gradient red-600→red-500, text white
- All buttons: 12px radius, padding 10px 24px

### Modals
- Overlay: #0f172a at 40% opacity + backdrop blur
- Modal box: WHITE bg, 16px radius, heavy shadow
- Header: frosted glass (white 80% + blur), title bold, close X button
- Content area: white, 24px padding

### Badges/Status Pills
- Small rounded-full pills
- Approved: light green bg #dcfce7, green text #16a34a
- Pending: light orange bg #fff7ed, orange text #f97316
- Rejected: light red bg #fef2f2, red text #dc2626
- Size: 10px bold text, padding 2px 8px

### Search Input
- Same as input field but with magnifying glass icon on the left

### Pagination
- Active page: bg #130F26, text white, rounded
- Inactive: WHITE bg, border, text #475569

### Notification Dropdown
- Bell icon (24px), with red count badge
- Dropdown: WHITE bg, 384px wide, 16px radius, heavy shadow
- Each item: icon + title + message + time

### SweetAlert Dialogs
- Rounded 20px, WHITE bg, centered
- Confirm button: #130F26 bg, full width
- Cancel button: #f1f5f9 bg, gray text

---

## Page Types

### 1. Login Page
- Split screen: Left half = full bleed image with primary color overlay, Right half = WHITE form area
- Form: centered, max 384px wide
- Title "Sign in": 30px, extra bold, #130F26
- Inputs with labels
- Full-width primary button
- Footer: copyright text

### 2. Dashboard
- Stat cards in 4-column grid (responsive)
- Charts below
- WHITE cards on light gray page background

### 3. Data Table Pages
- Page header: title + subtitle + action buttons
- Filter bar: dropdowns + search input
- WHITE table card
- Pagination

### 4. Form Pages (Create/Edit)
- Back button
- WHITE form cards with sections
- 2-column grid layout for form fields
- Save button

### 5. Modal CRUD Pages (Divisi, Jabatan, Shift, Role, etc.)
- Table page with "+ Tambah" button
- Centered modal overlay for create/edit
- Same table + modal pattern

### 6. Calendar Page (Jadwal)
- Month view calendar
- Color-coded shift events
- Legend bar

---

## Mobile App (Flutter)

- Light theme matching web
- Background: #F8FAFC
- Cards: WHITE
- Primary accent: #130F26
- Bottom navigation bar
- Card-based layouts

---

## Animations

- Button hover: lift 0.5px up + glow shadow
- Card hover: lift 1px up + stronger shadow  
- Sidebar icon hover: scale to 110%
- Modal: fade + slide up + scale
- Dropdown: fade + slide down
- Table row hover: lift with shadow
- All transitions: 200-300ms ease
