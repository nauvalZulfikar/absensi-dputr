<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PASSWORD USER</title>
</head>

<body style="font-family: 'Arial', sans-serif;">

    <div style="background-color: #f4f4f4; padding: 20px; text-align: center;">
        <h1>Selamat Datang!</h1>
    </div>

    <div style="padding: 20px;">
        @if(isset($mailData['password']))
        <p>
            Kami senang Anda telah bergabung dengan kami. Berikut adalah password baru Anda: <strong>{{ $mailData['password'] }}</strong>
        </p>

        <p>
            Harap simpan password dengan aman dan jangan berikan kepada orang lain.
        </p>
        @else
            <p>
                Anda telah terdaftar dalam project dengan detail informasi sebagai berikut:
            </p>
            <table>
                <thead>
                    <tr>
                        <td>Nama :</td>
                        <td>{{ $mailData['nameUser'] }}</td>
                    </tr>
                    <tr>
                        <td>Nama Project :</td>
                        <td>{{ $mailData['projectName'] }}</td>
                    </tr>
                    <tr>
                        <td>No Project :</td>
                        <td>{{ $mailData['projectNo'] }}</td>
                    </tr>
                    <tr>
                        <td>Waktu Mulai :</td>
                        <td>{{ $mailData['startdate'] }}</td>
                    </tr>
                    <tr>
                        <td>Target Selesai :</td>
                        <td>{{ $mailData['targetdate'] }}</td>
                    </tr>
                    <tr>
                        <td>Alamat :</td>
                        <td>{{ $mailData['address'] }}</td>
                    </tr>
                    <tr>
                        <td>Jam Masuk :</td>
                        <td>{{ $mailData['timeInShift'] }}</td>
                    </tr>
                    <tr>
                        <td>Jam Pulang :</td>
                        <td>{{ $mailData['timeOutShift'] }}</td>
                    </tr>
                   
                </thead>
            </table>
        @endif
        <p>
            Jika Anda memiliki pertanyaan atau memerlukan bantuan, jangan ragu untuk menghubungi kami. Kami siap membantu Anda!
        </p>

        <p>
            Terima kasih atas kepercayaan Anda.
        </p>
        <p>
            Salam hangat,<br>
            Dinas PUPR Kabupate Bandung
        </p>
    </div>

</body>

</html>