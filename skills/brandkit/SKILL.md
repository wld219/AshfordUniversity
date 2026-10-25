---
name: brandkit
description: Skill tạo/định hướng brand kit: màu, chữ, logo vibe, mockup, không chỉ làm đẹp bề mặt.
---

# Brandkit Skill VI

Dùng khi cần định hình thương hiệu trước UI.

## Thành phần brand kit

- định vị: ai dùng, vì sao tin
- cảm giác: mạnh, gần gũi, cao cấp, kỹ thuật, dân dụng, cộng đồng
- màu chính/phụ/trung tính
- typography: heading/body/mono nếu cần
- icon style
- ảnh/minh hoạ style
- quy tắc không dùng

## Output

```text
BRAND_READ: ...
COLOR_SYSTEM: ...
TYPE_SYSTEM: ...
IMAGE_STYLE: ...
UI_APPLICATION: ...
DO_NOT_USE: ...
```

## Với listing Marketplace / devtool

Ưu tiên rõ ràng, đáng tin, thực tế — screenshot thật, changelog rõ, không quá “startup giả”. Bằng chứng thật (repo, license, publisher) mạnh hơn mockup bóng bẩy.

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
