---
name: design-taste-frontend
description: Design Director Core — chống UI AI rập khuôn cho dev/SaaS/dashboard/devtool/docs/agent UI. Design Read → Taste Direction → UI Plan → Pre-Flight → code.
---

# DMCTN Taste Skill — Design Director Core (v0.3)

Dùng cho **developer, frontend, dashboard, SaaS, devtool, docs, AI agent workspace, app UI**.  
Không dùng preset ngành cá nhân (điện máy, sửa chữa, cửa hàng địa phương) trong core — chỉ tham chiếu brief người dùng nếu họ tự mô tả.

**Luật:** Không code UI trước khi hoàn thành **Pre-Flight Check Lite** với `PASS_TO_CODE`.

Tham chiếu gate: `.cursor/rules/dmctn-taste-gate.mdc` (R2).

---

## 0. Design Read (bắt buộc)

Trước khi sửa file, đọc và trả block:

```text
DESIGN_READ:
- Product type: (SaaS / devtool console / admin dashboard / docs portal / agent workspace / marketing dev / redesign / mobile utility)
- Target user: (dev / PM / ops / end user — cụ thể)
- User intent: (họ muốn làm gì trên màn hình này?)
- Primary action: (1 hành động chính)
- Content hierarchy: (thứ tự ưu tiên nội dung)
- Technical constraints: (stack, DS có sẵn, a11y, perf, không đổi API/route)
```

Một dòng tóm tắt:

> Đọc brief này là: `<product type>` cho `<user>`, intent `<intent>`, nghiêng về `<style/system>`.

Tín hiệu bổ sung: tài sản có sẵn (logo, màu, font, UI hiện tại), ràng buộc (a11y, pháp lý, tốc độ).

---

## 1. Taste Direction

```text
TASTE_DIRECTION:
- Developer preset: (chọn 1 từ § Developer Preset Pack)
- Visual density: (1-10)
- Motion level: (1-10)
- Color direction:
- Typography direction:
- Component direction:
DIALS:
- DESIGN_VARIANCE: (1-10) — 1 an toàn, 10 phá cách
- MOTION_INTENSITY: (1-10) — 1 tĩnh, 10 cinematic
- VISUAL_DENSITY: (1-10) — 1 thoáng, 10 dày
```

Gợi ý dial theo ngữ cảnh **dev**:

| Ngữ cảnh | Variance | Motion | Density |
|---|---:|---:|---:|
| Devtool console | 5-6 | 2-4 | 6-7 |
| SaaS product | 6-7 | 3-5 | 4-5 |
| Admin dashboard | 4-6 | 1-3 | 6-8 |
| Docs / portal | 4-5 | 1-2 | 5-6 |
| AI agent workspace | 6-7 | 3-5 | 5-6 |
| Design system starter | 5-6 | 2-3 | 5-6 |
| Redesign existing | 5-7 | 2-4 | 5-7 |

---

## 2. UI Plan

```text
UI_PLAN:
- Layout structure:
- Component list:
- Responsive plan: (360 / 768 / 1280)
- Accessibility plan:
- State plan: (loading / empty / error / success)
- Edge cases:
```

---

## 3. Pre-Flight Check Lite

Trả **YES/NO** từng mục:

```text
PRE_FLIGHT_LITE:
1. Brief understood? YES/NO
2. Page type identified? YES/NO
3. Main user action clear? YES/NO
4. Visual direction selected? YES/NO
5. Anti-slop risks identified? YES/NO
6. Responsive plan ready? YES/NO
7. Accessibility risks checked? YES/NO
8. Component states considered? YES/NO
9. Copy tone selected? YES/NO
10. Verdict: PASS_TO_CODE / NEEDS_MORE_BRIEF
```

---

## 4. Anti-Slop Check

FAIL nếu dự định (không có lý do rõ):

- gradient tím/cyan AI mặc định
- glassmorphism vô nghĩa
- 3 feature card ngang rập khuôn
- CTA glow vô lý
- generic SaaS hero (headline + 2 CTA + mockup lơ lửng)
- emoji/icon lộn xộn
- animation loop vô nghĩa
- stock copy (“revolutionary”, “seamless”, “elevate”)
- Inter + slate + rounded card mặc định khi không cần

```text
ANTI_SLOP_RISKS: ...
```

---

## 5. Design system map (chọn một)

- Microsoft / enterprise → Fluent UI
- Material-flavored → Material 3
- Shopify admin → Polaris
- GitHub / devtool → Primer-style
- Trust / public sector → GOV.UK / USWDS-style
- Indie SaaS / custom → Tailwind + component sở hữu (customize shadcn, không default)

**Một hệ thống mỗi project.**

---

## 6. Typography & layout

- Không mặc định Inter.
- Heading có nhịp; body ~60-75 ký tự/dòng; tối đa 2 họ font có lý do.
- Grid có nhịp; một điểm nhớ thị giác; không mọi section = title + paragraph + card.
- Mobile-first; desktop mở rộng có chủ đích.

---

## 7. Motion

- Motion phục vụ dẫn mắt / phản hồi / chuyển trạng thái.
- `prefers-reduced-motion` bắt buộc.
- Không loop vô hạn trừ khi phục vụ hiểu nội dung.

---

## 8. Self Review (trước PR / chốt design)

```text
SELF_REVIEW:
- Brief alignment: PASS/FAIL
- Visual hierarchy: PASS/FAIL
- Originality: PASS/FAIL
- Accessibility: PASS/FAIL
- Mobile: PASS/FAIL
- Component quality: PASS/FAIL
- Verdict: PASS_TO_CODE / NEEDS_POLISH / NEEDS_REDESIGN
```

Sau khi có UI: dùng `skills/ui-review-skill/SKILL.md` cho **Design QA Score**.

---

## Developer Preset Pack R1

Chọn **một** preset làm north star. Chi tiết component: `skills/component-taste/SKILL.md`.

### 1. Developer Tool Console

| | |
|---|---|
| **Best for** | CLI companion, IDE panel, log viewer, config UI |
| **Avoid** | marketing hero, gradient showcase, decorative glass |
| **Color** | neutral dark/light; accent 1 màu cho action |
| **Typography** | mono cho code; sans geometric cho UI |
| **Layout** | sidebar + main; dense toolbar |
| **Components** | bordered panels, compact tables, monospace blocks |
| **CTA** | solid primary; không glow |
| **Motion** | 1-3 |
| **Density** | 6-8 |
| **PASS** | scan nhanh, keyboard-friendly |
| **FAIL** | landing-style hero trong console |

### 2. SaaS Product UI

| | |
|---|---|
| **Best for** | product app, onboarding, settings |
| **Avoid** | 3-card feature row, purple gradient hero |
| **Color** | brand tint nhẹ + neutral surface |
| **Typography** | sans readable; heading có weight contrast |
| **Layout** | app shell; sidebar hoặc top nav có lý do |
| **Components** | cards có hierarchy; forms rõ label |
| **CTA** | một primary per view |
| **Motion** | 3-5 |
| **Density** | 4-5 |
| **PASS** | primary action rõ trong 3s |
| **FAIL** | template SaaS không đổi từ brief |

### 3. Admin Dashboard

| | |
|---|---|
| **Best for** | ops, analytics, CRUD admin |
| **Avoid** | cinematic motion, decorative illustration |
| **Color** | high-contrast data; semantic status colors |
| **Typography** | compact sans; tabular nums |
| **Layout** | grid widgets; filter bar sticky |
| **Components** | tables, filters, KPI tiles |
| **CTA** | secondary cho bulk; primary cho save |
| **Motion** | 1-3 |
| **Density** | 6-8 |
| **PASS** | empty/loading/error mọi widget |
| **FAIL** | mất số liệu khi “làm đẹp” |

### 4. AI Agent Workspace

| | |
|---|---|
| **Best for** | chat + tools + run history |
| **Avoid** | sci-fi gradient, avatar carousel |
| **Color** | calm neutral; accent cho send/run |
| **Typography** | readable 15-16px body; code blocks mono |
| **Layout** | split: thread + context panel |
| **Components** | message list, tool cards, status chips |
| **CTA** | send/stop rõ; không pulse vô hạn |
| **Motion** | 3-5 |
| **Density** | 5-6 |
| **PASS** | hierarchy message vs system vs tool |
| **FAIL** | chat bubble giống mọi AI demo |

### 5. Docs / Developer Portal

| | |
|---|---|
| **Best for** | API docs, guides, changelog |
| **Avoid** | marketing fluff trên trang reference |
| **Color** | light/dark readable; link contrast cao |
| **Typography** | prose + mono code; heading anchor |
| **Layout** | left nav + content + optional TOC |
| **Components** | code blocks, callouts, version badge |
| **CTA** | copy code, try API — không glow |
| **Motion** | 1-2 |
| **Density** | 5-6 |
| **PASS** | scan heading, search hoạt động |
| **FAIL** | thiếu code sample contrast |

### 6. Marketplace Listing Page

| | |
|---|---|
| **Best for** | extension/app store listing |
| **Avoid** | fake metrics, stock hero |
| **Color** | trust + screenshot frame neutral |
| **Typography** | clear H1; scannable bullets |
| **Layout** | hero proof (screenshot thật) + features + install |
| **Components** | media gallery, compatibility badges |
| **CTA** | install/get — một primary |
| **Motion** | 2-4 |
| **Density** | 4-5 |
| **PASS** | screenshot thật, copy cụ thể |
| **FAIL** | generic “boost productivity” |

### 7. Mobile Utility App

| | |
|---|---|
| **Best for** | utility, settings-light, field tool |
| **Avoid** | desktop layout thu nhỏ |
| **Color** | high contrast outdoor; dark mode |
| **Typography** | 16px+ body; short labels |
| **Layout** | bottom nav / FAB có lý do |
| **Components** | large touch targets, sheets |
| **CTA** | thumb-zone primary |
| **Motion** | 2-4 |
| **Density** | 4-5 |
| **PASS** | one-hand primary flow |
| **FAIL** | tap target < 44px |

### 8. Design System Starter

| | |
|---|---|
| **Best for** | tokens, primitives, doc site nội bộ |
| **Avoid** | marketing page làm DS |
| **Color** | token scale documented |
| **Typography** | scale rõ; không font ngẫu nhiên |
| **Layout** | component gallery + usage |
| **Components** | mọi state documented |
| **CTA** | copy token / view code |
| **Motion** | 2-3 |
| **Density** | 5-6 |
| **PASS** | button/card có matrix state |
| **FAIL** | chỉ screenshot không spec |

### 9. Redesign Existing UI

| | |
|---|---|
| **Best for** | nâng UI giữ flow (xem `redesign-skill`) |
| **Avoid** | đổi route/field; sơn màu |
| **Color** | map từ brand hiện có |
| **Typography** | cải thiện hierarchy, không đổi font vô lý |
| **Layout** | inventory trước; mode refresh/refactor/rebrand/rebuild |
| **Components** | keep/remove/improve list |
| **CTA** | giữ vị trí quen thuộc khi có thể |
| **Motion** | 2-4 |
| **Density** | theo brief |
| **PASS** | before/after có lý do |
| **FAIL** | mất chức năng |

### 10. Image-to-Code UI

| | |
|---|---|
| **Best for** | implement từ mockup/screenshot |
| **Avoid** | đoán spacing; bỏ a11y |
| **Color** | lấy từ ảnh + token project |
| **Typography** | đo tỷ lệ, không default Inter |
| **Layout** | grid từ ảnh; responsive suy luận có ghi chú |
| **Components** | map 1:1 có uncertainty list |
| **CTA** | khớp ảnh |
| **Motion** | theo ảnh hoặc tối thiểu |
| **Density** | theo ảnh |
| **PASS** | side-by-side khớp chính |
| **FAIL** | generic template thay ảnh |

---

## Quy trình bắt buộc

1. Design Read → Taste Direction → UI Plan.
2. Pre-Flight Check Lite → chỉ `PASS_TO_CODE` mới code.
3. Anti-Slop + component rules khi implement.
4. Self Review; nếu cần: UI Review Skill + Design QA Score.

## Output tổng hợp (trước code)

```text
DESIGN_READ: ...
TASTE_DIRECTION: ...
DIALS: variance= / motion= / density=
UI_PLAN: ...
PRE_FLIGHT_LITE: (10 mục + verdict)
ANTI_SLOP_RISKS: ...
VERDICT: PASS_TO_CODE | NEEDS_MORE_BRIEF
```
