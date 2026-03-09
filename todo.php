<?php
/*
TODO:: add Widgets in manage resources:
    -students:: Display last 5 birthdays and 5 coming birthdays or TBD shotuld i put it as Overview modal?
    -attendances::
    -lessons:: completion dates alert
    -assessments:: upcoming dates alert
    -fee collections:: upcoming dates alert
TODO:: grade overview
TODO:: Grades: review computations on components and etc
TODO:: table seeders: fee collections
TODO:: check all table search if there is an error, including the modals and tables.
TODO:: check all tables column sortable/order
TODO:: import student
TODO:: export student
TODO:: download active classs in excel
TODO:: download app backup
TODO:: licensed Payment

--END

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
Filters
    -StudentFilters.php
        ->genderFilter()

