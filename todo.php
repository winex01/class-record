<?php
/*
TODO:: refactor transfer all forms/fields into the schema

TODO:: Notification & calendar for:
        * Lessons Completion date
        * Assessments date
        * Fee collections date

TODO:: Grade Summary (Overall Grade) action modal / Manage Grae Overview

TODO:: find a way to that the attendance will be converted to assessment if they want

TODO:: Grades: review computations on components and etc

TODO:: table seeders: fee collections

TODO:: check all tables column sortable/order
TODO:: check all table search if there is an error, including the modals and tables.

TODO:: import student
TODO:: export student
TODO:: download active classs in excel
TODO:: download app backup


Architecture convention
Forms
    -StudentForm.php
        ->schema()
        ->selectOptions()
Columns
    -StudentColumns.php
        ->schema()
Actions
    -StudentActions.php
        ->createAction()
        ->createRangesAction()

