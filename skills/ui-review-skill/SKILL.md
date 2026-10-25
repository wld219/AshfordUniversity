---
name: ui-review-skill
description: UI Review R1 — đánh giá UI đã build với Design QA Score và verdict READY/NEEDS_POLISH/NEEDS_REDESIGN/FAIL.
---

# DMCTN UI Review Skill (R1)

Dùng **sau khi có UI** (hoặc trước merge PR) cho dev/SaaS/dashboard/devtool/docs/agent UI.

Không thay thế Pre-Flight trước code — skill này **review kết quả**.

---

## Output bắt buộc

```text
UI_REVIEW:
1. First impression: (3 giây đầu — user hiểu gì?)
2. Brief alignment: (PASS/FAIL + note)
3. Visual hierarchy: (PASS/FAIL + note)
4. Layout / spacing: (PASS/FAIL + note)
5. Typography: (PASS/FAIL + note)
6. Color / contrast: (PASS/FAIL + note)
7. Component quality: (PASS/FAIL + note)
8. Mobile / responsive: (PASS/FAIL + note)
9. Accessibility: (PASS/FAIL + note)
10. AI-slop signs: (list hoặc NONE)
11. Fix plan: (ưu tiên P0/P1/P2)
12. Design QA Score: (bảng điểm bên dưới)
13. Verdict: READY | NEEDS_POLISH | NEEDS_REDESIGN | FAIL
```

---

## Design QA Score (tổng 100)

| Tiêu chí | Điểm tối đa |
|----------|------------:|
| Brief alignment | 15 |
| Visual hierarchy | 15 |
| Originality | 15 |
| Brand fit | 15 |
| Component quality | 10 |
| Mobile / responsive | 10 |
| Accessibility | 10 |
| Anti-slop | 10 |

Ghi từng hạng mục: `điểm / max` và **tổng**.

---

## Verdict theo điểm

| Tổng | Verdict |
|------|---------|
| 90–100 | **READY** |
| 75–89 | **NEEDS_POLISH** |
| 60–74 | **NEEDS_REDESIGN** |
| &lt;60 | **FAIL** |

---

## AI-slop signs (checklist)

- purple/cyan gradient mặc định
- glassmorphism vô nghĩa
- 3-card row template
- CTA glow
- generic SaaS hero
- emoji/icon noise
- meaningless motion loop
- stock copy

Nếu có ≥2 dấu hiệu không giải thích → Anti-slop ≤5/10.

---

## Fix plan format

```text
FIX_PLAN:
- P0: (block release)
- P1: (should fix before ship)
- P2: (polish)
```

Tham chiếu chi tiết component: `skills/component-taste/SKILL.md`.
