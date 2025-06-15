<?xml version="1.0" encoding="UTF-8"?>

<?php

/*
    I RID SEPA funzionano solo con la valuta Euro
*/
$currency = defaultCurrency();

$count_rows = 0;
$count_total = 0;
$index = 1;

foreach($users as $user) {
    if ($user->currentBalanceAmount($currency) < 0 && !empty($user->rid['iban'])) {
        $count_rows++;
        $count_total += $user->currentBalanceAmount($currency);
    }
}

$count_total = $count_total * -1;

?>
<urn:CBIBdySDDReq xmlns:urn="urn:CBI:xsd:CBIBdySDDReq.00.01.00">
    <urn:PhyMsgInf>
        <urn:PhyMsgTpCd>INC-SDDC-01</urn:PhyMsgTpCd>
        <urn:NbOfLogMsg>1</urn:NbOfLogMsg>
    </urn:PhyMsgInf>
    <urn:CBIEnvelSDDReqLogMsg>
        <urn:CBISDDReqLogMsg>
            <urn1:GrpHdr xmlns:urn1="urn:CBI:xsd:CBISDDReqLogMsg.00.01.00">
                <urn1:MsgId>{{ Illuminate\Support\Str::random(20) }}</urn1:MsgId>
                <urn1:CreDtTm>{{ $date . date('\TH:i:s.000') }}</urn1:CreDtTm>
                <urn1:NbOfTxs>{{ $count_rows }}</urn1:NbOfTxs>
                <urn1:CtrlSum>{{ $count_total }}</urn1:CtrlSum>
                <urn1:InitgPty>
                    <urn1:Nm>{{ __('texts.generic.uppercare_gas_heading') }}</urn1:Nm>
                    <urn1:Id>
                        <urn1:OrgId>
                            <urn1:Othr>
                                <urn1:Id>{{ $currentgas->rid['org'] ?? '' }}</urn1:Id>
                                <urn1:Issr>CBI</urn1:Issr>
                            </urn1:Othr>
                        </urn1:OrgId>
                    </urn1:Id>
                </urn1:InitgPty>
            </urn1:GrpHdr>
            <urn1:PmtInf xmlns:urn1="urn:CBI:xsd:CBISDDReqLogMsg.00.01.00">
                <urn1:PmtInfId>1</urn1:PmtInfId>
                <urn1:PmtMtd>DD</urn1:PmtMtd>
                <urn1:PmtTpInf>
                    <urn1:SvcLvl>
                        <urn1:Cd>SEPA</urn1:Cd>
                    </urn1:SvcLvl>
                    <urn1:LclInstrm>
                        <urn1:Cd>CORE</urn1:Cd>
                    </urn1:LclInstrm>
                    <urn1:SeqTp>RCUR</urn1:SeqTp>
                </urn1:PmtTpInf>
                <urn1:ReqdColltnDt>{{ date('Y-m-d', strtotime($date . ' +5 days')) }}</urn1:ReqdColltnDt>
                <urn1:Cdtr>
                    <urn1:Nm>GRUPPO DI ACQUISTO {{ strtoupper($currentgas->name) }}</urn1:Nm>
                </urn1:Cdtr>
                <urn1:CdtrAcct>
                    <urn1:Id>
                        <urn1:IBAN>{{ $currentgas->rid['iban'] }}</urn1:IBAN>
                    </urn1:Id>
                </urn1:CdtrAcct>
                <urn1:CdtrAgt>
                    <urn1:FinInstnId>
                        <urn1:ClrSysMmbId>
                            <urn1:MmbId>{{ iban_split($currentgas->rid['iban'], 'abi') }}</urn1:MmbId>
                        </urn1:ClrSysMmbId>
                    </urn1:FinInstnId>
                </urn1:CdtrAgt>
                <urn1:CdtrSchmeId>
                    <urn1:Id>
                        <urn1:PrvtId>
                            <urn1:Othr>
                                <urn1:Id>{{ $currentgas->rid['id'] }}</urn1:Id>
                            </urn1:Othr>
                        </urn1:PrvtId>
                    </urn1:Id>
                </urn1:CdtrSchmeId>
                @foreach($users as $user)
                    @if ($user->currentBalanceAmount($currency) < 0 && !empty($user->rid['iban']))
                        <urn1:DrctDbtTxInf>
                            <urn1:PmtId>
                                <urn1:InstrId>{{ $index++ }}</urn1:InstrId>
                                <urn1:EndToEndId>{{ $user->username }}</urn1:EndToEndId>
                            </urn1:PmtId>
                            <urn1:InstdAmt Ccy="EUR">{{ round($user->currentBalanceAmount($currency) * -1, 2) }}</urn1:InstdAmt>
                            <urn1:DrctDbtTx>
                                <urn1:MndtRltdInf>
                                    <urn1:MndtId>{{ $user->rid['id'] }}</urn1:MndtId>
                                    <urn1:DtOfSgntr>{{ date('Y-m-dP', strtotime($user->rid['date'])) }}</urn1:DtOfSgntr>
                                </urn1:MndtRltdInf>
                            </urn1:DrctDbtTx>
                            <urn1:Dbtr>
                                <urn1:Nm>{{ strtoupper($user->printableName()) }}</urn1:Nm>
                            </urn1:Dbtr>
                            <urn1:DbtrAcct>
                                <urn1:Id>
                                    <urn1:IBAN>{{ $user->rid['iban'] }}</urn1:IBAN>
                                </urn1:Id>
                            </urn1:DbtrAcct>
                            <urn1:RmtInf>
                                <urn1:Ustrd>{{ $body }}</urn1:Ustrd>
                            </urn1:RmtInf>
                        </urn1:DrctDbtTxInf>
                    @endif
                @endforeach
            </urn1:PmtInf>
        </urn:CBISDDReqLogMsg>
    </urn:CBIEnvelSDDReqLogMsg>
</urn:CBIBdySDDReq>
