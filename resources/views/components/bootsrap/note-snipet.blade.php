<style>
    .note{
        display: inline-block;
        min-width: 50%;
        max-width: 100%;
    }
</style>

<div class="note {{ isset($class) ? $class : '' }} mb-4">
    <div class="bg-info" style="border-left:5px solid rgb(203, 175, 175); padding:10px;">
        <span class="fw-bold">&#128394 NOTE</span> <br>
        <p class="text-muted" style="padding:10px 20px;">
            {{ $slot }}
        </p>
    </div>
</div>