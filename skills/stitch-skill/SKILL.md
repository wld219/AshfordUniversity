---
name: stitch-design-taste
description: Xuất quy tắc thiết kế dạng DESIGN.md/semantic để agent khác hoặc Google Stitch dùng lại.
---

# Stitch Design Taste VI

Dùng khi cần xuất thiết kế thành tài liệu tái sử dụng.

## Output DESIGN.md

Phải có:

- Product context
- Audience
- Brand feeling
- Color tokens
- Type scale
- Spacing scale
- Component rules
- Motion rules
- Accessibility rules
- Anti-slop rules

## Không xuất mô tả mơ hồ

Sai: “giao diện đẹp, hiện đại”.
Đúng: “header 64px, nền #0B0F14, CTA chính high-contrast, card dùng border 1px, shadow rất nhẹ”.

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
