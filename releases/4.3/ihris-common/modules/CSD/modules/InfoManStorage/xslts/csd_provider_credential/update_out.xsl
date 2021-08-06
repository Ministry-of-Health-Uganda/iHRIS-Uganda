<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:careServicesRequest xmlns:csd="urn:ihe:iti:csd:2013" xmlns="urn:ihe:iti:csd:2013">             
      <function urn="urn:openhie.org:openinfoman-hwr:stored-function:health_worker_update_credential">
	<!-- DOES NOT REALLY DO ANYTHING AS THERE IS NO DATA TO UDPATE -->
	<requestParams>                     
	  <id><xsl:attribute name='entityID'><xsl:value-of select="substring-before(/form/@id,'/')"/></xsl:attribute></id>                     
	  <credential>
	    <codedType>
	      <xsl:attribute name="codingScheme"><xsl:value-of select="substring-before(/form/field[@name='csd_provider_credential_type']/value,'@@@')"/></xsl:attribute>
	      <xsl:attribute name="code"><xsl:value-of select="substring-after(/form/field[@name='csd_provider_credential_type']/value,'@@@')"/></xsl:attribute>
	    </codedType>
	    <xsl:if test="/form/field[@name='number']">
	      <number><xsl:value-of select="/form/field[@name='number']"/></number>
	    </xsl:if>

	    <xsl:if test="/form/field[@name='issuing_authority']">
	      <issuingAuthority><xsl:value-of select="/form/field[@name='issuing_authority']"/></issuingAuthority>
	    </xsl:if>
	    <xsl:if test="/form/field[@name='renewal_date']">
	      <xsl:variable name='renew' select="/form/field[@name='renewal_date']"/>
	      <credentialRenewalDate><xsl:value-of select="$renew/year"/>-<xsl:value-of select="$renew/month"/>-<xsl:value-of select="$renew/day"/></credentialRenewalDate>
	    </xsl:if>
	    <xsl:if test="/form/field[@name='issue_date']">
	      <xsl:variable name='issue' select="/form/field[@name='issue_date']"/>
	      <credentialIssueDate><xsl:value-of select="$issue/year"/>-<xsl:value-of select="$issue/month"/>-<xsl:value-of select="$issue/day"/></credentialIssueDate>
	    </xsl:if>

	  </credential>
	</requestParams>             
      </function>         
    </csd:careServicesRequest>     
  </xsl:template> 
</xsl:stylesheet>
