---
name: industrial-brutalist-ui
description: Phong cách brutalist/cơ khí: mạnh, thô, rõ cấu trúc, dùng có kiểm soát.
---

# Brutalist Skill VI

Dùng khi brief cần cảm giác mạnh, kỹ thuật, cơ khí, hacker, industrial.

## Đặc điểm

- border rõ
- grid lộ cấu trúc
- mono/type mạnh
- contrast cao
- ít bo góc
- màu accent dứt khoát

## Cảnh báo

Brutalist dễ thành khó dùng. Không áp dụng cho app khách hàng phổ thông nếu không có lý do.

## Dials

```text
DESIGN_VARIANCE: 7-9
MOTION_INTENSITY: 2-5
VISUAL_DENSITY: 5-8
```

## PASS khi

Giao diện mạnh nhưng vẫn đọc được, thao tác được, không làm người dùng phổ thông lạc đường.

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
