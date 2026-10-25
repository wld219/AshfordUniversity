---
name: image-to-code
description: Skill biến ảnh/wireframe/mockup thành giao diện code sát ý, không đoán bừa.
---

# Image to Code Skill VI

Dùng khi người dùng đưa ảnh UI, wireframe, screenshot hoặc mockup.

## Quy trình

1. Mô tả ảnh: layout, typography, màu, spacing, component, trạng thái.
2. Phân biệt phần chắc chắn và phần cần suy luận.
3. Xác định breakpoint mobile/tablet/desktop.
4. Tạo component map trước khi code.
5. Code sát ảnh nhưng vẫn giữ accessibility/performance.

## Không được

- Đọc ảnh xong tự đổi phong cách khác.
- Bịa màu/spacing nếu có thể đoán hợp lý từ ảnh.
- Bỏ qua chi tiết tương tác: hover, focus, active, empty.
- Dùng ảnh làm nền thay vì dựng UI thật, trừ khi user yêu cầu.

## Output

```text
IMAGE_READ: ...
COMPONENT_MAP: ...
UNCERTAIN_PARTS: ...
IMPLEMENTATION_PLAN: ...
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
