<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:csd="urn:ihe:iti:csd:2013"
  exclude-result-prefixes="csd xsl"
  >     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:CSD xmlns:csd="urn:ihe:iti:csd:2013">
      <csd:organizationDirectory>
	<xsl:for-each select="/csd:CSD/csd:organizationDirectory/csd:organization">
	  <csd:organization>
	    <xsl:attribute name="entityID"><xsl:value-of select="./@entityID"/></xsl:attribute>
	    <xsl:for-each select="./csd:otherID">
	      <csd:otherID><xsl:attribute name="id"><xsl:value-of select="../@entityID"/>:<xsl:value-of select="@position"/></xsl:attribute></csd:otherID>
	    </xsl:for-each>
	  </csd:organization>
	</xsl:for-each>
      </csd:organizationDirectory>
      <csd:providerDirectory/>
      <csd:serviceDirectory/>
      <csd:facilityDirectory/>
    </csd:CSD>
  </xsl:template>       
</xsl:stylesheet>
