---
name: component-taste
description: Component Taste Rules R1 — must/avoid/a11y/mobile cho UI dev/SaaS/dashboard.
---

# DMCTN Component Taste Rules (R1)

Áp dụng khi implement hoặc review từng component. Mỗi block: **Must have**, **Must avoid**, **A11y**, **Mobile**, **PASS/FAIL signs**.

---

## Button

- **Must have:** label rõ; primary/secondary phân biệt; focus ring; disabled/loading state
- **Must avoid:** glow mặc định; quá nhiều primary trên một view
- **A11y:** `aria-busy` khi loading; contrast AA
- **Mobile:** min height ~44px; full-width primary khi cần
- **PASS:** một primary action rõ / view
- **FAIL:** 3+ nút cùng weight “primary”

---

## Card

- **Must have:** hierarchy (title → meta → action); padding nhất quán
- **Must avoid:** mọi card cùng shadow/bo góc template
- **A11y:** heading level đúng; clickable card = button/link semantic
- **Mobile:** stack; không chữ tràn không ellipsis plan
- **PASS:** scan được trong 2s
- **FAIL:** card chỉ là border + icon generic

---

## Navbar

- **Must have:** current location; collapse mobile
- **Must avoid:** 10+ top-level items desktop-only
- **A11y:** skip link; keyboard nav
- **Mobile:** hamburger/drawer có focus trap
- **PASS:** primary nav ≤7 items
- **FAIL:** nav tràn ngang mobile

---

## Sidebar

- **Must have:** section labels; active state; scroll độc lập
- **Must avoid:** sidebar + top nav trùng chức năng
- **A11y:** `aria-current` trên item active
- **Mobile:** ẩn/collapse; không chiếm 50% viewport
- **PASS:** ops user tìm menu trong 1 lần nhìn
- **FAIL:** icon-only không tooltip/label

---

## Hero

- **Must have:** lý do tồn tại (value prop cụ thể); CTA gắn primary action
- **Must avoid:** centered headline + 2 CTA + floating mockup mặc định
- **A11y:** H1 một; không text trong ảnh
- **Mobile:** CTA trong thumb zone; ảnh không đẩy CTA xuống quá sâu
- **PASS:** user hiểu product trong 3s
- **FAIL:** stock subtitle “All-in-one platform”

---

## Form / Input

- **Must have:** label visible; error inline; required indicator
- **Must avoid:** placeholder thay label
- **A11y:** `aria-invalid`, `describedby` cho error
- **Mobile:** input 16px+ font tránh zoom iOS
- **PASS:** submit error tổng hợp + field-level
- **FAIL:** chỉ border đỏ không message

---

## Table

- **Must have:** header sticky (khi dài); empty state; sort/filter nếu data lớn
- **Must avoid:** table không scroll trên mobile khi >4 cột
- **A11y:** `scope` header; caption khi cần
- **Mobile:** card row fallback hoặc horizontal scroll có chỉ báo
- **PASS:** đọc được số liệu chính trên 360px
- **FAIL:** truncate mất cột quan trọng

---

## Modal

- **Must have:** focus trap; close rõ; title
- **Must avoid:** modal chồng modal không stack plan
- **A11y:** `role="dialog"`; return focus on close
- **Mobile:** full-screen sheet khi cần
- **PASS:** Esc + backdrop close
- **FAIL:** scroll body phía sau

---

## Toast

- **Must have:** auto-dismiss có thể; action optional
- **Must avoid:** toast che primary CTA
- **A11y:** `role="status"` / `alert` đúng mức
- **Mobile:** bottom safe area
- **PASS:** không stack >3
- **FAIL:** toast permanent không dismiss

---

## Empty state

- **Must have:** giải thích + next action
- **Must avoid:** illustration generic không hướng dẫn
- **A11y:** heading mô tả trạng thái
- **Mobile:** CTA reachable
- **PASS:** user biết làm gì tiếp
- **FAIL:** “No data” một dòng

---

## Pricing

- **Must have:** plan compare rõ; highlight recommended có lý do
- **Must avoid:** fake urgency; hidden fees UI
- **A11y:** table semantics hoặc list có giá đọc được
- **Mobile:** stack plans; toggle monthly/yearly reachable
- **PASS:** giá và limit đọc được không hover
- **FAIL:** chỉ gradient card không spec

---

## Dashboard widget

- **Must have:** title, metric, trend, loading/empty/error
- **Must avoid:** chart trang trí không số
- **A11y:** text alternative cho chart chính
- **Mobile:** widget stack; không 4 cột ép
- **PASS:** ops đọc KPI trong 5s
- **FAIL:** animation che số liệu

---

## Chat panel

- **Must have:** phân role message; timestamp; input sticky
- **Must avoid:** bubble gradient AI cliché
- **A11y:** live region có kiểm soát (không spam)
- **Mobile:** keyboard không che input
- **PASS:** phân biệt user/assistant/system
- **FAIL:** infinite scroll không anchor

---

## Settings page

- **Must have:** nhóm setting; save/discard; dangerous zone tách
- **Must avoid:** flat list 40 mục không group
- **A11y:** switch có label; description
- **Mobile:** full width controls
- **PASS:** tìm setting trong 2 tap
- **FAIL:** toggle không có trạng thái loading khi save async

---

## Cách dùng

1. Trước implement: liệt kê component trong `UI_PLAN`.
2. Khi review: map FAIL sign → Fix plan trong `ui-review-skill`.
