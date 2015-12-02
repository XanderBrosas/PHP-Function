<?php
 
 
error_reporting(E_ALL);
date_default_timezone_set('UTC');
 

function LocalDate($format, $time = null, $timezone = 'UTC')
{
    if (is_null($time))
        $time = time();
 
    $local = new DateTime('@'.$time);
    $local->setTimeZone(new DateTimeZone($timezone));
 
    return $local->format($format);
}
 
function GetTimezones()
{
    $now = time(); 
 
    $times = array();
 
    $start = $now - date('G', $now) * 3600;
    for($i = 0; $i < 24*60; $i += 15)
        $times[date('g:i A', $start + $i * 60)] = array();
 
    $timezones = DateTimeZone::listIdentifiers();
    foreach($timezones AS $timezone)
    {
        $dt = new DateTime('@'.$now);
        $dt->setTimeZone(new DateTimeZone($timezone));
 
        $time = $dt->format('g:i A');
 
        $times[$time][] = $timezone;
    }
 
    return array_filter($times);
}
 
 
$user_timezone = isset($_POST['timezone']) ? $_POST['timezone'] : 'America/New_York';
 
$tzs = GetTimezones();
 
?>
<html>
<head>
    <title>Dynamic Time Zones</title>
 
    <style type="text/css">
        body { font-family: arial, sans-serif; font-size: 0.9em; }
        label, select { display: block; }
    </style>
</head>
<body>
 
<?php if (isset($_POST['timezone']) && !empty($_POST['timezone'])): ?>
    <b>The current time in <?php echo htmlspecialchars($_POST['timezone']); ?> is
        <?php echo LocalDate('r', time(), $_POST['timezone']); ?></b>
    <br/><br/>
<?php endif; ?>
 
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
    <div id="timezonepicker">
        <label>Closest time zone:
            <select name="timezone">
                <option value="">--</option>
                <?php foreach($tzs AS $time => $timezones): foreach($timezones AS $tz): ?>
                    <option value="<?php echo $tz; ?>"<?php if ($user_timezone == $tz) echo ' selected'; ?>>
                        <?php echo str_replace(array('/', '_'), array(': ', ' '), $tz); ?> (<?php echo $time; ?>)
                    </option>
                <?php endforeach; endforeach; ?>
            </select>
        </label>
 
        <input type="submit" value="Set Time Zone"/>
    </div>
</form>
 
 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
 
<script type="text/javascript">
var tzs = <?php echo json_encode($tzs); ?>; 
 
function TimeZonePicker()
{
    var picker = $('#timezonepicker'),
        time = null,
        timezone = $('select[name=timezone]', picker),
        current = $('option:selected', timezone).val();
 
    picker.prepend(
        $('<label/>').text('Current time:').append(
            $('<select/>').attr('name', 'time')));
    time = $('select[name=time]', picker);
 
    for(var it in tzs)
    {
        time.append(
            $('<option/>').attr('value', it).text(it));
 
        for(var itz in tzs[it])
        {
            if (tzs[it][itz] == current)
                time.val(it); 
        }
    }
 
    var timechange = function () {
        var newtime = $('#timezonepicker select[name=time]').val(),
            timezones = tzs[newtime];
 
        if (timezones)
        {
            timezone.html('').removeAttr('disabled');
            for(var itz in timezones)
            {
                var prettyname = timezones[itz].split('/').slice(1).join(', ').replace('_', ' ');
                if (prettyname)
                {
                    timezone.append(
                        $('<option/>')
                            .attr('selected', current == timezones[itz])
                            .attr('value', timezones[itz]).text(prettyname));
                }
            }
        }
    };
 
    time.change(timechange);
    timechange();  
}
 
$(function () {
    TimeZonePicker();
});
</script>
 
</body>
</html>