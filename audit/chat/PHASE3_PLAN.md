# Phase 3 Plan - File Renaming and Cleanup

## Objective
Replace obsolete `teacher_*` and `student_*` files with `conference_*` and `auditeur_*` respectively.

## Current State Analysis

### Files Using Teacher Prefix (for conference.php / index.php)

| Old File | Used By | New File | Exists? | Action |
|----------|---------|----------|---------|--------|
| js/teacher-def.js | conference.php, index.php | js/conference-def.js | ❌ No | Create copy + update refs |
| js/teacher-func.js | index.php | js/conference-func.js | ❌ No | Create copy + update refs |
| js/teacher-post.js | index.php | js/conference-post.js | ❌ No | Create copy + update refs |
| js/teacher-ms.js | index.php (commented) | js/conference-ms.js | ❌ No | Create copy + update refs |
| styles/teacher.css | index.php | styles/conference.css | ✅ Yes | Update refs only |
| styles/teacher.min.css | Not currently used | styles/conference.min.css | ❌ No | Create copy if needed |

### Files Using Student Prefix (for Student.php / subtitles.php)

| Old File | Used By | New File | Exists? | Action |
|----------|---------|----------|---------|--------|
| js/student-def.js | Student.php, subtitles.php | js/auditeur-def.js | ✅ Yes | Update refs only |
| js/student-func.js | Student.php, subtitles.php | js/auditeur-func.js | ✅ Yes | Update refs only |
| js/student-post.js | Student.php | js/auditeur-post.js | ✅ Yes | Update refs only |
| styles/student.css | Student.php, subtitles.php | styles/auditeur.css | ✅ Yes | Update refs only |
| styles/student-specific.css | ? | styles/auditeur-specific.css | ✅ Yes | Update refs if used |

### Section IDs to Rename

| Old ID | File | New ID | Action |
|--------|------|-------|--------|
| teacher-session | conference.php | conference-session | Update |
| teacher-session-title | conference.php | conference-session-title | Update |

## Implementation Plan

### Step 1: Create Missing conference_* Files

For files that don't have a conference_* equivalent yet, create a copy of the teacher_* file:

1. **js/conference-def.js** - Copy from js/teacher-def.js
2. **js/conference-func.js** - Copy from js/teacher-func.js
3. **js/conference-post.js** - Copy from js/teacher-post.js
4. **js/conference-ms.js** - Copy from js/teacher-ms.js
5. **styles/conference.min.css** - Copy from styles/teacher.min.css (if exists)

### Step 2: Update References in PHP Files

#### conference.php
```php
// Current:
$pageScripts = ["/js/teacher-def.js", ...];

// Change to:
$pageScripts = ["/js/conference-def.js", ...];

// Also update:
<section id="teacher-session" ...> -> <section id="conference-session" ...
<h2 id="teacher-session-title"> -> <h2 id="conference-session-title">
```

#### index.php
```php
// Current:
echo "<script src=\"js/teacher-def.js?v=".\$classroomid."\"></script>";
echo "<script src=\"js/teacher-func.js?v=".\$classroomid."\"></script>";
// ...
<link href="styles/teacher.css" rel="stylesheet">

// Change to:
echo "<script src=\"js/conference-def.js?v=".\$classroomid."\"></script>";
echo "<script src=\"js/conference-func.js?v=".\$classroomid."\"></script>";
// ...
<link href="styles/conference.css" rel="stylesheet">
```

#### Student.php
```php
// Current:
echo "<script src=\"js/student-def.js?v=".\$classroomid."\"></script>";
echo "<script src=\"js/student-func.js?v=".\$classroomid."\"></script>";
// ...
<link href="styles/student.css" rel="stylesheet">

// Change to:
echo "<script src=\"js/auditeur-def.js?v=".\$classroomid."\"></script>";
echo "<script src=\"js/auditeur-func.js?v=".\$classroomid."\"></script>";
// ...
<link href="styles/auditeur.css" rel="stylesheet">
```

#### subtitles.php
```php
// Current:
<script src="js/student-def.js"></script>
<script src="js/student-func.js"></script>
// ...
<link href="styles/student.css" rel="stylesheet">

// Change to:
<script src="js/auditeur-def.js"></script>
<script src="js/auditeur-func.js"></script>
// ...
<link href="styles/auditeur.css" rel="stylesheet">
```

### Step 3: Verify All Files Are Updated

Search for remaining references to old filenames:
```bash
grep -r "teacher-\|student-\|teacher_\|student_" /home/omist/*.php /home/omist/*.html 2>/dev/null
```

### Step 4: Delete Obsolete Files

After verifying all references are updated, delete the obsolete files:
```bash
# Teacher files (after creating conference equivalents)
rm /home/omist/js/teacher-def.js
rm /home/omist/js/teacher-func.js
rm /home/omist/js/teacher-post.js
rm /home/omist/js/teacher-ms.js
rm /home/omist/styles/teacher.css
rm /home/omist/styles/teacher.min.css

# Student files (after updating all references)
rm /home/omist/js/student-def.js
rm /home/omist/js/student-func.js
rm /home/omist/js/student-post.js
rm /home/omist/styles/student.css
rm /home/omist/styles/student-specific.css
```

### Step 5: Test All Interfaces

Verify that:
- conference.php loads and works correctly
- index.php loads and works correctly
- Student.php loads and works correctly
- subtitles.php loads and works correctly
- All JavaScript functions are accessible
- All CSS styles are applied correctly

## Detailed File Mapping

### JavaScript Files

#### Conference Interface
```
teacher-def.js    --> conference-def.js    (copy + update refs in conference.php, index.php)
teacher-func.js   --> conference-func.js   (copy + update refs in index.php)
teacher-post.js   --> conference-post.js   (copy + update refs in index.php)
teacher-ms.js     --> conference-ms.js     (copy + update refs in index.php if uncommented)
```

#### Auditeur Interface
```
student-def.js    --> auditeur-def.js     (update refs in Student.php, subtitles.php)
student-func.js   --> auditeur-func.js    (update refs in Student.php, subtitles.php)
student-post.js   --> auditeur-post.js    (update refs in Student.php)
```

### CSS Files

#### Conference Interface
```
teacher.css       --> conference.css       (update refs in index.php)
teacher.min.css   --> conference.min.css   (copy + update refs if used)
```

#### Auditeur Interface
```
student.css       --> auditeur.css        (update refs in Student.php, subtitles.php)
student-specific.css --> auditeur-specific.css (update refs if used)
```

## Verification Checklist

- [ ] js/conference-def.js created from js/teacher-def.js
- [ ] js/conference-func.js created from js/teacher-func.js
- [ ] js/conference-post.js created from js/teacher-post.js
- [ ] js/conference-ms.js created from js/teacher-ms.js
- [ ] styles/conference.min.css created from styles/teacher.min.css (if needed)
- [ ] conference.php references updated to conference-def.js
- [ ] index.php references updated to conference-*.js and conference.css
- [ ] Student.php references updated to auditeur-*.js and auditeur.css
- [ ] subtitles.php references updated to auditeur-*.js and auditeur.css
- [ ] Section IDs in conference.php updated (teacher-session -> conference-session)
- [ ] No remaining references to teacher_* or student_* in PHP files
- [ ] All obsolete files deleted
- [ ] All interfaces tested and working

## Rollback Plan

In case of issues:
1. Restore obsolete files from git
2. Revert changes in PHP files
3. Test again

## Benefits

1. **Consistency**: All conference-related files use `conference_*` prefix
2. **Consistency**: All auditeur-related files use `auditeur_*` prefix
3. **Maintainability**: Clear file naming convention
4. **Reduced confusion**: No more mixing teacher/student/conference/auditeur
5. **Better organization**: Files grouped by interface type

## Notes

- `Student.php` should ideally be renamed to `Intervenant.php` or `Conference.php` in a future phase, but this requires more careful consideration as it's a major file
- The `index.php` file seems to be the conference interface, so it should use `conference_*` files
- Ensure all copied files maintain their original functionality
