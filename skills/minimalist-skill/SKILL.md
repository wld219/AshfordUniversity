---
name: minimalist-ui
description: Phong cách tối giản nhưng không nhạt: hierarchy rõ, chữ mạnh, spacing chính xác.
---

# Minimalist UI Skill VI

Tối giản không có nghĩa là trắng trơn.

## Nguyên tắc

- ít màu nhưng hierarchy rõ
- ít component nhưng mỗi component có vai trò
- font/spacing là yếu tố chính
- nội dung ngắn, sắc
- nhiều khoảng trắng có chủ đích

## Dials

```text
DESIGN_VARIANCE: 4-6
MOTION_INTENSITY: 1-3
VISUAL_DENSITY: 2-4
```

## FAIL nếu

- chỉ xoá hết màu rồi gọi là tối giản
- thiếu CTA rõ
- thiếu trạng thái
- mobile quá trống hoặc quá dài

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
