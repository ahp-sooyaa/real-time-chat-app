<?php

namespace App\Models;

use Endroid\QrCode\QrCode as EndroidQrCode;
use Illuminate\Support\Str;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;

class QrCode extends Model
{
    use HasFactory;

    public $guarded = [];

    /**
     * Get the user that owns the QrCode
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateFor($user)
    {
        // generate token and link for qr code
        $token = Str::random();
        $link = route('friend.add', [$user->id, $token]);

        // generate qr code with link
        $qrCode = EndroidQrCode::create($link)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Validate the result
        // $writer->validateResult($result, $link);

        // make storage directory for qr codes
        Storage::makeDirectory('public/qr-codes');

        // save qr code image to storage directory
        $imagePath = 'qr-codes/' . $user->id . '_qrcode.png';
        $qrCodeStoragePath = storage_path('app/public/') . $imagePath;
        $result->saveToFile($qrCodeStoragePath);

        // record generated qr code and link in database
        QrCode::create([
            'user_id' => $user->id,
            'link' => urldecode($link),
            'image_path' => $imagePath,
            'token' => $token,
        ]);
    }
}
