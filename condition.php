<?php
// this is a list of medical conditions currently given to the user to select from on their profile page
// the file will be used as options in an html form dropdown menu:

// columns to the left of the arrow function => are the array keys
// this array is fed in to a list of dropdown options in a loop
// check condition_dropdown.php i use that file more often
$conditions_array = array(
"Not Provided." => "\nSELECT OPTION",
"ibsd" => "IBS-D (diarrhea predominant)",
"ibsc" => "IBS-C (constipation predominant)",
"ibsa" => "IBS-A (alternating diarrhea/constipation)",
"ibspi" => "IBS-PI (post-infectious)",
"pdvibs" => "PDV-IBS (post-diverticulitis)",
"bipolar" => "Bipolar Disorder",
"cancer" => "Cancer",
"chronicc" => "Chronic Idiopathic Constipation",
"celiac" => "Celiac Disease",
"fatigue" => "Chronic Fatigue Syndrome",
"chrones" => "Crohns Disease",
"depression" => "Depression",
"anxiety" => "Anxiety",
"lactose" => "Lactose Intolerance",
"ocd" => "Obsessive Compulsive Disorder",
"ptsd" => "Post Traumatic Stress Disorder",
"colitis" => "Ulcerative Colitis"
);