---
name: imagegen-frontend-web
description: Tạo prompt ảnh tham chiếu web cao cấp trước khi code frontend.
---

# ImageGen Frontend Web VI

Dùng để tạo ảnh tham chiếu cho website/landing page trước khi code.

## Prompt ảnh phải có

- loại trang
- người dùng mục tiêu
- cảm giác thương hiệu
- bố cục chính
- typography
- màu chủ đạo
- độ dày nội dung
- điểm nhớ thị giác
- điều cần tránh

## Công thức prompt

```text
Create a premium website reference image for [product], targeting [audience].
Visual direction: [brand feeling].
Layout: [specific layout, not generic SaaS hero].
Typography: [font mood].
Color system: [palette].
Avoid: purple AI gradients, generic 3-card SaaS layout, excessive glassmorphism.
```

## Sau khi có ảnh

Agent phải phân tích ảnh thành DESIGN.md rồi mới code.

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
