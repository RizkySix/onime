<style>
    .main-button {
        
        display: inline-block;
                    outline: none;
                    cursor: pointer;
                    font-weight: 600;
                    border-radius: 3px;
                    padding: 12px 24px;
                    border: 0;
                    color: #fff;
                    background: #000a47;
                    line-height: 1.15;
                    font-size: 16px;
                    :hover {
                        transition: all .1s ease;
                        box-shadow: 0 0 0 0 #fff, 0 0 0 3px #1de9b6;
                    }
                
    }
</style>

<button type="{{ $type }}" class="main-button {{ isset($class) ? $class : '' }}" >
    {{ $slot }}
</button>