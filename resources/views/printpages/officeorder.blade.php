<html>

<head>
    <style>
        table {
            border-collapse: collapse;
            border: 1px solid black;
        }

        table,
        table tr td,
        table tr th {
            page-break-inside: avoid;
        }

        @media print {

            table,
            table tr td,
            table tr th {
                page-break-inside: avoid;
            }
        }

        th,
        td {
            border: 1px solid black;
        }

        body {
            padding-top: 100px !important;
            padding-left: 20px;
            padding-right: 20px;
            line-height: 23px !important;
            font-size: 15.5px;
        }

        header {
            position: fixed;
            left: 0px;
            right: 0px;
            height: 100px;
        }

        footer {
            position: fixed;
            margin-left: 4.5%;
            width: 85%;
            bottom: 24px;
            padding-top: 15px;
            line-height: 18px !important;
        }

        @page {
            margin: 25px 25px 50px 25px;
            padding-bottom: 20px;
            padding-top: 30px;
        }

        [data-f-id="pbf"] {
            display: none !important;
        }
    </style>
</head>
<?php
$opciones_ssl = [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
];

$img_path = 'images/MDSig.png';
$extencion = pathinfo($img_path, PATHINFO_EXTENSION);
$data = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
$img_base_64 = base64_encode($data);
$path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;

// $img_pathBG = 'images/letterhead - small.jpg';
$img_pathBG = 'images/bccl_letter_head_logo.png';
$extencionBG = pathinfo($img_pathBG, PATHINFO_EXTENSION);
$dataBG = file_get_contents($img_pathBG, false, stream_context_create($opciones_ssl));
$img_base_64BG = base64_encode($dataBG);
$path_imgBG = 'data:image/' . $extencion . ';base64,' . $img_base_64BG;

$img_pathFooter = 'images/bccl_letter_footer_logo.png';
$extencionFooter = pathinfo($img_pathFooter, PATHINFO_EXTENSION);
$dataFooter = file_get_contents($img_pathFooter, false, stream_context_create($opciones_ssl));
$img_base_64Footer = base64_encode($dataFooter);
$path_imgFooter = 'data:image/' . $extencion . ';base64,' . $img_base_64Footer;
?>

<body style="background-image: url('{{ $path_imgBG }}'); background-repeat: no-repeat;background-position: 51% 0%; ">

    <footer>
        <hr />
        <img src="{{ $path_imgFooter }}"
            style="background-repeat: no-repeat; background-position: 5% 98%; max-width: 100%; max_height: 100%;">

    </footer>

    <main style="padding-left:13px;padding-right:13px;padding-top:10px;">
        <div>
            <table style="width:100%;border:none;">
                <tr>
                    <td style="width:50%;border:none;"><strong>Ref No.: </strong>{{ $referenceNo }}</td>
                    <td style="width:20%;margin-left:30%;text-align:right;border:none;"><strong>Date:
                        </strong>{!! $date !!}</td>
                </tr>
            </table>
            <br />

            <table style="width:100%;border:none; font-size: 14px;">
                <tr>
                    <td style='width:50%;border:none;'><strong>{{ $employeeName }}
                            &nbsp;({{ $employeeEmpId }})
                        </strong></td>

                </tr>
                <tr>
                    @if ($pmsOutcomeId === '4')
                        <td style='width:50%;border:none;'>
                            <strong>{{ $newEmployeeDesignation ?? $employeeDesignation }}, ({{ $employeeDept }})
                            </strong>
                        </td>
                    @else
                        <td style='width:50%;border:none;'><strong>{{ $employeeDesignation }},
                                ({{ $employeeDept }})</strong></td>
                    @endif
                </tr>

                <tr>
                    <td style='width:50%;border:none;'>
                        <strong>Bhutan Carbide and Chemical Limited</strong>
                    </td>
                </tr>
            </table>
            <br />

            {{-- @if (!$notOrder)
                <h3><center><strong><u>OFFICE ORDER</u></strong></center></h3>
            @endif
            {!! $content !!} --}}
        </div>
        <table style="border:none;">

            <br />
            <br />
            <br />
            <br />
            <tr>
                <td colspan="2" style="border:none;">
                    <strong>(Name of Competent Signature) <br />
                        Position Title
                    </strong>
                </td>
            </tr>

        </table>
        <br />
        <br />
        <table style="border:none;">
            <tr>
                <td colspan="2" style="border:none;">
                    Copy to: <br>
                    {!! $cc !!}
                </td>
            </tr>
        </table>
    </main>


    <script>
        window.print();
    </script>
</body>

</html>
