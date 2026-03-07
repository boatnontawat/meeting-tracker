<?php

// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // 🌟 อย่าลืมเพิ่มบรรทัดนี้ด้านบน

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // 🌟 เพิ่มคำสั่งตั้งค่า Trust Proxies ตรงนี้ครับ
        $middleware->trustProxies(at: '*'); 
        
        // หรือถ้าต้องการระบุ Header ให้ชัดเจนสำหรับ Cloudflare:
        /*
        $middleware->trustProxies(headers: 
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_AWS_ELB
        );
        */
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();;
