---
name: high-end-visual-design
description: Phong cách mềm, cao cấp, nhiều khoảng trắng, ít ồn, dùng cho brand/app cần cảm giác tinh tế.
---

# Soft Skill VI

Dùng cho giao diện cần cảm giác calm, premium, expensive-looking.

## Đặc điểm

- khoảng trắng rộng
- tương phản vừa phải nhưng vẫn đọc tốt
- border/shadow nhẹ
- motion chậm, mượt, ít
- typography sạch
- màu trung tính + 1 accent

## Tránh

- quá nhiều glow
- gradient mạnh
- card dày đặc
- icon quá nhiều
- text dài không nhịp
- animation phô diễn

## Dials

```text
DESIGN_VARIANCE: 5-7
MOTION_INTENSITY: 2-4
VISUAL_DENSITY: 2-4
```

## Quy trình bắt buộc

1. Đọc brief và inventory hiện có.
2. Xác định người dùng thật, không thiết kế theo gu mơ hồ.
3. Chọn cảm giác thương hiệu trước khi chọn màu/component.
4. Đặt ba biến: `DESIGN_VARIANCE`, `MOTION_INTENSITY`, `VISUAL_DENSITY`.
5. Chỉ dùng hiệu ứng nếu nó giúp người dùng hiểu hoặc thao tác tốt hơn.
6. Thiết kế mobile-first, sau đó mới mở rộng desktop.
7. Trả về PASS/FAIL rõ ràng trước khi code.

## Anti-slop mặc định

Không mặc định dùng:

- gradient tím/xanh kiểu AI SaaS
- hero căn giữa + subtitle + 2 CTA + mockup lơ lửng
- 3 feature card ngang giống nhau
- glassmorphism toàn bộ giao diện
- font Inter/slate mặc định khi không có lý do
- icon/emoji lộn xộn
- animation vô hạn gây phân tâm

## Output trước khi làm

```text
DESIGN_READ: ...
DIALS: variance/motion/density = ...
PLAN: ...
ANTI_SLOP_RISKS: ...
VERDICT: PASS_TO_CODE hoặc NEEDS_BRIEF
```
