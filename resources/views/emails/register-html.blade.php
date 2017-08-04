<!DOCTYPE html>
<html>
<body>

  Hi {!! $member->username !!}, <br /><br />

  Welcome to Mirrorizer. You need verify your account to activate. Please click or open below link in browser:<br /><br />

  <a href="{!! $activationLink !!}">{!! $activationLink !!}</a><br /><br />

  Thank you!

</body>
</html>